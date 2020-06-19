<?php 

require_once '../'.('library' . DIRECTORY_SEPARATOR . 'app.php');

use Extension\UpsellManager\UpsellManager;
use Application\Session;
//echo Session::get(sprintf('steps.%s.orderId',Session::get('steps.current.id')));
$upsellmanagerObj = new UpsellManager();
$config = $upsellmanagerObj->getAppRunConfig();
// print_r($config);die;
$config['tpl_vars']['currentUpsellDetails']['tokens'] = array(
    'id' => '1'
);

App::run($config);

?>