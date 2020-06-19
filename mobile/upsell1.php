<?php

require_once (dirname(__DIR__) . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'app.php');

App::run(array(
	'config_id' => 2,
	'step' => 2,
	'tpl' => 'upsell1.tpl',
	'go_to' => 'upsell2.php',
	'tpl_vars' => array(),
	'version' => 'mobile',
	'pageType' => 'upsellPage1',
));
