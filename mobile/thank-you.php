<?php
require_once (dirname(__DIR__) . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'app.php');

App::run(array(
    'step'      => 3,
    'version'   => 'mobile',
    'tpl'       => 'thank-you.tpl',
    'go_to'     => '',
    'tpl_vars'  => array(),
    'pageType'  => 'thankyouPage',
));
