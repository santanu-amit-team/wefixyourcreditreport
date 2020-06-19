<?php
date_default_timezone_set('UTC');

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php';

Bootstrap::initialize();

touch(sprintf('%s%s.cron_running_status', STORAGE_DIR, DS));

use Application\Cron;

Cron::init();
