<?php
require_once (dirname(__DIR__) . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'app.php');

App::run(array(
    'config_id' => 3,
    'step'      => 3,
    'tpl'       => 'upsell2.tpl',
    'go_to'     => 'thank-you.php',
    'tpl_vars'  => array(),
    'version'   => 'mobile',
    'pageType'  => 'upsellPage2',
));
