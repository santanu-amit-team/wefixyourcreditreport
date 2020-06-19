<?php
require (dirname(__FILE__) . '/library/app.php');

App::run(array(
    'config_id' => 2,
    'step'      => 2,
    'tpl'       => 'upsell1.tpl',
    'go_to'     => 'thank-you',
    'tpl_vars'  => array(),
    'version'   => 'desktop',
    'pageType'  => 'upsellPage1',
));
