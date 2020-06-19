<?php
require_once ('library' . DIRECTORY_SEPARATOR . 'app.php');

App::run(array(
    'step'      => 4,
    'version'   => 'desktop',
    'tpl'       => 'thank-you.tpl',
    'go_to'     => '',
    'tpl_vars'  => array(),
    'pageType'  => 'thankyouPage',
));