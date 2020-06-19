<?php
require_once ('library' . DIRECTORY_SEPARATOR . 'app.php');

App::run(array(
    'config_id' => 1,
    'step'      => 1,
    'tpl'       => 'discount.tpl',
    'go_to'     => 'upsell1.php',
    'version'   => 'desktop',
    'tpl_vars'  => array(),
    'pageType'  => 'leadPage',
));
