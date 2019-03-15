<?php
require_once __DIR__ . '/../config/defines.php';
require_once __DIR__ . '/../config/utils.php';

class SimpleLoader
{

    public function autoLoader()
    {
        spl_autoload_register([$this, 'loadClass']);
    }

    protected function loadClass($name)
    {
        $name = str_replace('\\', '/', $name);
        $file_name = APP_ROOT . '/' . $name . '.php';
        if (file_exists($file_name)) {
            require_once $file_name;
        }
    }

    public function run()
    {
        $params = $_GET;
        $uri = fetch($params, '_r');
        list($controller, $action, $id) = explode('/', $uri);
        if (empty($controller)) {
            $controller = 'index';
        }
        if (empty($action)) {
            $action = 'index';
        }
        $controllerCamel = underLineString2Camel($controller);
        $class_file = CONTROLLER_DIR . '/' . $controllerCamel . '.php';
        info('before run', 'class_file', $class_file);
        if (file_exists($class_file)) {
            $class_name = "\controllers\\{$controllerCamel}";
            info($class_name);
            require_once $class_file;
            if (method_exists($class_name, $action)) {
                $visit_controller = new $class_name();
                $visit_controller->id = $controller;
                $visit_controller->action = $action;
                $visit_controller->run($id);
                return true;
            }
        }
        header("HTTP/1.1 404 Not Found");
    }
}