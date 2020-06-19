<?php
require_once ('library' . DIRECTORY_SEPARATOR . 'app.php');

App::run(array(
    'config_id'    => 1,
    'step'         => 1,
    'tpl'          => 'index',
    'go_to'        => 'checkout',
    'version'      => 'desktop',
    'tpl_vars'     => array(),
    'pageType'     => 'leadPage',
    'resetSession' => true
));
