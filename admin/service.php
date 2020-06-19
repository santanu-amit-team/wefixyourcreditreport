<?php

$library_dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'library';
require_once $library_dir . DIRECTORY_SEPARATOR . 'bootstrap.php';

Bootstrap::initialize('admin');

use Admin\Library\Auth;
use Application\Config;
use Application\Helper\Provider;
use Application\Helper\Security;
use Application\Request;
use Application\Session;
use Lazer\Classes\Database;
use Database\Connectors\ConnectionFactory;
use Admin\Controller\UrlPermissionController;

$restricted_controllers = array(
    'Auth'
);

function snakecase_to_camelcase($snakecase, $class = false)
{
    $parts     = explode('-', $snakecase);
    $camelcase = implode('', array_map('ucfirst', $parts));
    if ($class) {
        return $camelcase;
    }
    return lcfirst($camelcase);
}

function get_action_from_path($path = '')
{
    $information = explode('/', $path);
    if (count($information) < 2) {
        http_response_code(404);
        echo "Not found!";
        exit(1);
    }
    $table_name = $information[0];
    $namespace  = "Admin\\Controller\\";
    $class_name = $namespace . snakecase_to_camelcase(
        array_shift($information), true
    ) . 'Controller';
    $method_name = snakecase_to_camelcase(
        array_shift($information)
    );
    if (!method_exists($class_name, $method_name)) {
        http_response_code(404);
        echo "Not found!";
        exit(1);
    }
    return array(
        'table_name'  => $table_name,
        'class_name'  => $class_name,
        'method_name' => $method_name,
        'arguments'   => $information,
    );
}

$pathInfo = \Bootstrap::isNginxServer()
? Request::get('route')
: Request::getPathInfo();


$uri_path = parse_url($pathInfo, PHP_URL_PATH);
$uri_segments = explode('/', $uri_path);


$action_information = get_action_from_path(
    trim($pathInfo, '/')
);

array_walk(
    $restricted_controllers, function (&$controllerName) {
        $controllerName = sprintf(
            'Admin\Controller\%sController', $controllerName
        );
    }
);


header("Content-Type: application/json");
$class_name_parts = explode("\\", $action_information['class_name']);

echo json_encode(call_user_func_array(
    array(
        new $action_information['class_name']($action_information['arguments']),
        $action_information['method_name'],
    ), $action_information['arguments']
));
