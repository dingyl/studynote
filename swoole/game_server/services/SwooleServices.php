<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 18/2/23
 * Time: 下午9:24
 */

namespace services;

use base\Connects;

class SwooleServices extends \BaseModel
{

    static $_only_cache = true;

    private $local_server_ip;
    private $local_server_port;
    private $side_server_ip;
    private $side_server_port;

    private $web_socket;

    function __construct(array $values = array())
    {
        parent::__construct($values);
        $this->initService();
    }

    function initService()
    {
        // 初始化ip和port
        $this->side_server_ip = self::config('websocket_side_server_ip');
        $this->side_server_port = self::config('websocket_side_server_port');
        $this->local_server_ip = self::config('websocket_local_server_ip');
        $this->local_server_port = self::config('websocket_local_server_port');


        $this->web_socket = new \swoole_websocket_server($this->side_server_ip, $this->side_server_port);
        $this->web_socket->set([
            'debug_mode' => 1,
            'daemonize' => true,
            'dispatch_mode' => 2,
            'reload_async' => true,
            'max_request' => self::config('websocket_max_request'),
            'reactor_num' => self::config('websocket_reactor_num'),
            'worker_num' => self::config('websocket_worker_num'),
            'log_file' => APP_ROOT . 'log/websocket_server.log',
            'pid_file' => APP_ROOT . 'log/pids/websocket_server.pid',
            //心跳检测
//            'heartbeat_idle_time' => 60 * 5,
//            'heartbeat_check_interval' => 60
        ]);

        //$this->web_socket->on('Start', [$this, 'onStart']); // Server启动在主进程的主线程回调此函数
        $this->web_socket->on('WorkerStart', [$this, 'onWorkerStart']); // worker进程创建成功后调用
        $this->web_socket->on('WorkerStop', [$this, 'onWorkerStop']);
        $this->web_socket->on('open', [$this, 'onOpen']); // 当WebSocket客户端与服务器建立连接并完成握手后会回调此函数
        $this->web_socket->on('message', [$this, 'onMessage']); // 当WebSocket服务器收到来自客户端的数据帧时会回调此函数。
        $this->web_socket->on('close', [$this, 'onClose']); // 客户端连接关闭后，在worker进程中回调此函数
        $this->web_socket->on('request', [$this, 'onRequest']); // http服务接收数据
    }

    // 启动server，创建进程，开始监听端口
    function startService()
    {
        info("------ <game services start> ------");
        # 清理房间数据信息
        $this->web_socket->start();
    }

    // 平滑重启worker进程(worker和task_worker)
    function reloadService()
    {
        info("------ <game services restart> ------");
        $this->web_socket->reload();
    }

    // 平滑关闭服务器
    function stopService()
    {
        info("------ <game services stop> ------");
        $this->web_socket->shutdown();
        //exit(0);
    }

    function onStart($server)
    {
        info("------ <game services onStart> ------");
    }

    function onWorkerStart($server, $worker_id)
    {
        $this->redis = \BaseModel::getHotWriteCache();
        info("<game services onWorkerStart>----worker_id", $worker_id, 'pid', posix_getpid());
    }

    // worker和task_worker退出回调
    function onWorkerStop($server, $worker_id)
    {
        info("------ <game services onWorkerStop> ------worker_id", $worker_id, 'pid', posix_getpid());
    }

    //Swoole\Http\Request@{{"fd":1,"header":"@","server":"@","request":null,"cookie":null,"get":"@","files":null,"post":null,"tmpfiles":null}}
    function onOpen($server, $request)
    {
        $connect = new Connects($server, $request->fd);
        $data = $request->get;
        info('初始化连接信息', $data);
        $connect->setData($data);
        $route = new Route($connect);
        $application = $route->getAppliaction();
        if ($application && method_exists($application, 'afterOpen')) {
            $application->afterOpen();
        }
    }

    function onClose($server, $fd, $reactorId)
    {
        $connect = new Connects($server, $fd);
        $route = new Route($connect);
        $application = $route->getAppliaction();
        if ($application && method_exists($application, 'beforeClose')) {
            $application->beforeClose();
        }
    }

    function onMessage($server, $frame)
    {
        $connect = new Connects($server, $frame->fd);
        $route = new Route($connect);
        $application = $route->getAppliaction();
        if ($application) {
            $send_data = json_decode($frame->data, true);
            info('客户端消息数据', $send_data);
            if (isset($send_data['action'])) {
                $action = $send_data['action'];
                if (!$application->unNeedCheckAction($action) && !$application->beforeAction()) {
                    info('检测不通过', $application->error_reason);
                    $server->push($frame->fd, $application->error_reason);
                } else {
                    $event = \Phalcon\Text::camelize($action);
                    if (method_exists($application, $event)) {
                        $data = isset($send_data['data']) ? $send_data['data'] : [];
                        $application->$event($data);
                    }
                }
            }
        }
    }

    function onRequest($request, $response)
    {
        $params = $request->get;
        $action = fetch($params, 'action');
        if (!$action) {
            return;
        }
        switch ($action) {
            case "stop":
                $response->end(" stop Service !");
                $this->stopService();
                break;
            case "reload":
                $response->end(" restart Service !");
                $this->reloadService();
                break;
            default:
                $response->end("action is not found !");
                break;
        }
    }
}