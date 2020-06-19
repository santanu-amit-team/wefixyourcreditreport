<?php
require_once ('library' . DIRECTORY_SEPARATOR . 'app.php');

App::run(array(
    'config_id' => 1,
    'step'      => 1,
    'tpl'       => 'checkout.tpl',
    'go_to'     => 'thank-you.php',
    'version'   => 'desktop',
    'tpl_vars'  => array(),
    'pageType'  => 'checkoutPage',
));
