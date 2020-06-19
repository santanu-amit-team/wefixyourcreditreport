<?php

namespace Application;

use GO\Scheduler;
use Application\Registry;
use Admin\Controller\CronController;
use Application\Request;

class Cron
{

	public static function getAssignedSchedules()
	{
		$systemCrons = Registry::system('crons');
		//$extensionCrons = Registry::extension('crons');
		$extensionCronsStructured = CronController::fetchCronByPriorityForExecution();

		// if (!empty($extensionCrons))
		// {
		// 	foreach ($extensionCrons as $crons)
		// 	{
		// 		foreach ($crons as $cron)
		// 		{
		// 			$extensionCronsStructured[] = $cron;
		// 		}
		// 	}
		// }
		
		if(empty($extensionCronsStructured)) {
			return $systemCrons;
		}

		return array_merge($systemCrons, $extensionCronsStructured);
	}

	public static function init()
	{
		$scheduler = new Scheduler();

		$root = dirname(dirname(__DIR__)) . DS;
		/**
		 * Loop thru each cron and trigger the respective handlers
		 * Will be bypass if the cron expression at $cron['every'] doesnt match
		 */
		foreach (self::getAssignedSchedules() as $cron)
		{
			if(file_exists($root . $cron['handler']) && !strcmp(pathinfo($root . $cron['handler'], PATHINFO_EXTENSION), 'php')) {
				
				$scheduler
				->php($root . $cron['handler'])
				->at($cron['every'])->before(function () use($cron) {
					
					CronController::createLog($cron['id'], $cron['handler'], gmdate("Y-m-d\TH:i:s\Z"), 'Scheduled', 'Scheduler job has been scheduled.');
				})
				->then(function ($output) use ($cron){
                                        if (DEV_MODE) {
                                            echo "<pre>";
                                            print_r($output);
                                        }
					CronController::createLog($cron['id'], $cron['handler'], gmdate("Y-m-d\TH:i:s\Z"), 'Executed', strip_tags(implode('.', $output)));					
				});
			}
			else {
				$scheduler
					->call(function() use ($cron, $root)
					{
						if (!empty($cron['handler']))
						{
							$handler = explode('@', $cron['handler']);
							return call_user_func_array(array(new $handler[0], $handler[1]), array());
						}
					})
					->at($cron['every'])->before(function () use($cron) {
						
						$cronId = !empty($cron['id']) ? $cron['id'] : 0;
						CronController::createLog($cronId, $cron['handler'], gmdate("Y-m-d\TH:i:s\Z"), 'Scheduled', 'Scheduler job has been scheduled.');
					})
					->then(function ($output) use ($cron){
						if (DEV_MODE) {
							echo "<pre>";
							print_r($output);
						}
						$cronId = !empty($cron['id']) ? $cron['id'] : 0;
						CronController::createLog($cronId, $cron['handler'], gmdate("Y-m-d\TH:i:s\Z"), 'Executed', strip_tags($output));					
					});
			}
		}

		$scheduler->run();
	}

}
