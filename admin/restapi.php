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

$restricted_ontrollers = array(
    'Advanced', 'Affiliates', 'Cron', 'Diagnosis', 'Extensions',
    'Rotators', 'UserManagement',
);

if (!Auth::isLoginAuthorized()) {
    http_response_code(401);
    echo "You are unauthorized to access this!";
    exit(1);
}

if (!Session::has('isValidLicenseKey')) {
    $domain         = Provider::removeSubDomain(trim(Request::getHttpHost(), '/'));
    $licenseKey     = Config::settings('license_key');
    $unifyAuthentication = Config::settings('unify_authentication_key');
    $isValidLisence = Security::isValidLicenseKey($domain, $licenseKey, $unifyAuthentication);

    Session::set('isValidLicenseKey', $isValidLisence);
}

function snakecase_to_camelcase($snakecase, $class = false)
{
    $parts     = explode('-', $snakecase);
    $camelcase = implode('', array_map('ucfirst', $parts));
    if ($class) {
        return $camelcase;
    }
    return lcfirst($camelcase);
}

function version_control($table_name, $id)
{
    if(!is_numeric($id))
    {
        backup_extension_data('extensionsConfig', $id);
        return;
    }
    $database_file_path = LAZER_DATA_PATH . DS . $table_name . '.data.json';
    $oldBackupFiles = array_reverse(glob(LAZER_BACKUP_PATH . DS . $table_name . '-*.data.json'));
    $oldData = json_decode(file_get_contents($oldBackupFiles[0]), true);
    if(!empty($oldData))
    {
        foreach ($oldData as $value)
        {
            if((int)$id == (int)$value['id'])
            {
                $prev_content = $value;
                break;
            }
        }
    }
    $backup_file_path   = LAZER_BACKUP_PATH . DS . $table_name . '-' . date('YmdHis') . '.data.json';
    if (!file_exists($database_file_path)) {
        return;
    }
    $curr_content = Database::table($table_name)
            ->where('id', '=', $id)->findAll()->asArray()[0];
    if(is_null($curr_content))
    {
        return;
    }
    
    $prev_content = md5(json_encode($prev_content));
    $curr_content = md5(json_encode($curr_content));
    if ($curr_content == $prev_content) {
        return;
    }

    if (!file_exists(LAZER_BACKUP_PATH)) {
        mkdir(LAZER_BACKUP_PATH);
    }
    /* delete of backup file number is 10 or more */
    $bkFiles = array();
    foreach (glob(LAZER_BACKUP_PATH . DS . $table_name . '-*.data.json') as $file) {
        $time           = str_replace($table_name . '-', "", basename($file, ".data.json"));
        $bkFiles[$time] = basename($file);
    }
    krsort($bkFiles, SORT_NUMERIC);
    if (sizeof($bkFiles) == 10) {
        $i = 0;
        foreach ($bkFiles as $key => $value) {
            $i = $i + 1;
            if ($i == 10) {
                unlink(LAZER_BACKUP_PATH . DS . $value);
            }
        }
    }
    /**/
    if (is_writable(LAZER_BACKUP_PATH)) {
        if (!copy($database_file_path, $backup_file_path)) {
            echo "Backup error!";
            exit(1);
        }
    }
}

function backup_extension_data($table_name, $id)
{
    $database_file_path = LAZER_DATA_PATH . DS . $table_name . '.data.json';
    $backup_file_path   = LAZER_BACKUP_PATH . DS . $table_name . '-' . date('YmdHis') . '.data.json';
    if (!file_exists($database_file_path)) {
        return;
    }
    
    $oldBackupFiles = array_reverse(glob(LAZER_BACKUP_PATH . DS . $table_name . '-*.data.json'));
    $oldData = json_decode(file_get_contents($oldBackupFiles[0]), true);
    if(!empty($oldData))
    {
        foreach ($oldData as $key => $value)
        {
            if((string)$id == (string)$key)
            {
                $prev_content = $value;
                break;
            }
        }
    }
    $curr_content = Config::extensionsConfig($id);

    $postData = Request::form()->all();
    if(isset($postData['extension_slug']))
    {
        return;
    }
    $prev_content = md5(json_encode($prev_content));
    $curr_content = md5(json_encode($curr_content));
    if($curr_content == $prev_content)
    {
        return;
    }

    if (!file_exists(LAZER_BACKUP_PATH)) {
        mkdir(LAZER_BACKUP_PATH);
    }
    /* delete of backup file number is 10 or more */
    $bkFiles = array();
    foreach (glob(LAZER_BACKUP_PATH . DS . $table_name . '-*.data.json') as $file) {
        $time           = str_replace($table_name . '-', "", basename($file, ".data.json"));
        $bkFiles[$time] = basename($file);
    }
    krsort($bkFiles, SORT_NUMERIC);
    if (sizeof($bkFiles) == 10) {
        $i = 0;
        foreach ($bkFiles as $key => $value) {
            $i = $i + 1;
            if ($i == 10) {
                unlink(LAZER_BACKUP_PATH . DS . $value);
            }
        }
    }
    /**/
    if (is_writable(LAZER_BACKUP_PATH)) {
        if (!copy($database_file_path, $backup_file_path)) {
            echo "Backup error!";
            exit(1);
        }
    }
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

$uriPermission = new UrlPermissionController();

$isValid = $uriPermission->isValid($uri_segments[1]);
$urlsToByPass = array( 'url-permission', 'auth' );

if(!in_array( $uri_segments[1] , $urlsToByPass) && !$isValid['success'])
{
    echo json_encode(array(
        'success' => false,
        'error_message' => 'Access blocked due to permission issue.'
    )); 
    exit(1);
}


$action_information = get_action_from_path(
    trim($pathInfo, '/')
);

$logMethods = array('edit', 'add', 'delete', 'editManageData', 'downloadExtension', 'uninstallExtension');
$extensions = Config::extensions();
$result['extensionadminLoggerActive'] = false;
foreach ($extensions as $extension)
{
    if ($extension['extension_slug'] !== 'AdminLogs')
    {
        continue;
    }
    if ($extension['active'] === true)
    {
        $result['extensionadminLoggerActive'] = true;
    }
    break;
}

if(in_array($action_information['method_name'], $logMethods) && $result['extensionadminLoggerActive']) {
    try{
        $connection = getDatabaseConnection();
        $method_name = $action_information['method_name'];
        $logs = $action_information['table_name'];
        if(preg_match("/edit/", $action_information['method_name'])){
            $method_name = 'edit';
        }
        
        if($method_name != 'add')
        {
            $logs = $action_information['table_name']. ' (Edited - '. @$action_information['arguments'][0].')';
        }
        
        $connection->table('adminlogs')->insert(array(
            'event' => $method_name,
            'logs' => $logs,
            'email' => Session::has('googleEmail') ? Session::get('googleEmail') : '',
            'ipAddress' => Request::getClientIp(),
            'created_on' => date('Y-m-d H:i:s', time()),
        ));
    } catch (Exception $ex) {
        
    }
}

function getDatabaseConnection()
{
    $factory = new ConnectionFactory();
    return $factory->make(array(
                'driver' => 'sqlite',
                'database' => STORAGE_DIR . DS . 'adminlogs.sqlite',
    ));
}

array_walk(
    $restricted_ontrollers, function (&$controllerName) {
        $controllerName = sprintf(
            'Admin\Controller\%sController', $controllerName
        );
    }
);

// if (
//     Session::get('userType') !== 'developer' &&
//     Session::get('userType') !== 'webmaster' &&
//     in_array($action_information['class_name'], $restricted_ontrollers)
// ) {
//     http_response_code(404);
//     echo "Not found!";
//     exit(1);
// }

header("Content-Type: application/json");
$class_name_parts = explode("\\", $action_information['class_name']);
if(
    Session::get('isValidLicenseKey') !== true &&
    !in_array(
        array_pop($class_name_parts), array(
            'DashboardController', 'SettingsController', 'VersionsController', 'UrlPermissionController', 'ExtensionsController'
        )
    )
){
    echo json_encode(array(
        'success' => false,
        'message' => 'System error!'
    )); 
    exit(0);
}

$classRes = json_encode(call_user_func_array(
    array(
        new $action_information['class_name']($action_information['arguments']),
        $action_information['method_name'],
    ), $action_information['arguments']
));

if(in_array($action_information['method_name'], $logMethods)){
    version_control(
        $action_information['table_name'], $action_information['arguments'][0]
    );
    echo $classRes;
}
else
{
    echo $classRes;
}
