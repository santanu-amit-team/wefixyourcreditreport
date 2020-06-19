<?php

require_once (dirname(__DIR__) . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'app.php');

App::run(array(
    'config_id'    => 1,
    'step'         => 1,
    'tpl'          => 'index.tpl',
    'go_to'        => 'checkout.php',
    'version'      => 'mobile',
    'tpl_vars'     => array(),
    'pageType'     => 'leadPage',
    'resetSession' => true,
    // 'ajaxDelay'    => 10, //In seconsds,
));
