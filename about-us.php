<?php
require_once ('library' . DIRECTORY_SEPARATOR . 'app.php');

App::run(array(
    'config_id' => 1,
    'step'      => 1,
    'tpl'       => 'about-us.tpl',
    'go_to'     => '',
    'version'   => 'desktop',
    'tpl_vars'  => array(),
    'pageType'  => '',
));