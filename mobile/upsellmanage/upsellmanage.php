<?php 

require_once (dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'app.php');


use Extension\UpsellManager\UpsellManager;
use Application\Session;

$upsellmanagerObj = new UpsellManager();
$config = $upsellmanagerObj->getAppRunConfig();
$config['tpl_vars']['currentUpsellDetails']['tokens'] = array(
    'id' => '1',
    'orderid' => "32141241",
    'custom1' => 3124,
);


App::run($config);



?>