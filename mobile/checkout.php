<?php

require_once (dirname(__DIR__) . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'app.php');

App::run(array(
	'config_id' => 1,
	'step' => 1,
	'tpl' => 'checkout.tpl',
	'go_to' => 'upsell1.php',
	'version' => 'mobile',
	'tpl_vars' => array(),
	'pageType' => 'checkoutPage',
));
