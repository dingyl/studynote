<?php

# 压测工具
class BenchUtil
{
    protected $start_time;
    protected $end_time;
    protected $tasks = [];
    protected $module_name = '';

    const BENCH_DIR = __DIR__;

    public static function autoLoad($bench_dir = null)
    {
        if (!$bench_dir) {
            $bench_dir = self::BENCH_DIR;
        }

        spl_autoload_register(function ($class_name) use ($bench_dir) {
            $file = $bench_dir . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $class_name) . '.php';
            file_exists($file) && include($file);
        });
    }

    public static function bench($models_dir)
    {
        foreach (glob($models_dir . '/*') as $model_file) {
            $path_info = pathinfo($model_file);
            $file_name = $path_info['filename'];
            if ($file_name != 'BaseTest') {
                $class_name = basename($models_dir) . '\\' . $file_name;
                $test_model = new $class_name(new static());
                $test_model->run();
            }
        }
    }

    public function setModuleName($module_name)
    {
        $this->module_name = $module_name;
    }

    # 添加任务
    public function addTask($name, $func, $params = [])
    {
        $task = ['name' => $name, 'func' => $func, 'params' => $params];
        array_push($this->tasks, $task);
    }

    # 执行任务
    public function run()
    {
        $this->start();
        foreach ($this->tasks as $index => $task) {
            $real_index = $index + 1;
            $start = microtime(true);
            $this->echoLine('[用例' . $real_index . ':' . $task['name'] . '#开始执行](' . date('Y-m-d H:i:s', $start) . ')');
            call_user_func_array($task['func'], $task['params']);
            $end = microtime(true);
            $this->echoLine('[用例' . $real_index . ':' . $task['name'] . '#执行结束](' . date('Y-m-d H:i:s', $end) . '),耗时' . ($end - $start) . 's');
        }
        $this->end();
    }

    protected function start()
    {
        $this->start_time = microtime(true);
        $this->echoLine('测试<' . $this->module_name . '>任务开始');
    }

    protected function end()
    {
        $this->end_time = microtime(true);
        $this->echoLine('测试<' . $this->module_name . '>任务结束,总耗时' . ($this->end_time - $this->start_time) . 's');
    }

    public static function echoLine($value)
    {
        if (is_bool($value)) {
            $value = var_export($value, true);
        }

        if (is_array($value)) {
            $data = [];
            foreach ($value as $k => $v) {
                $data[] = "$k=>$v";
            }
            $value = '[' . implode(',', $data) . ']';
        }

        echo $value . PHP_EOL;
    }
}