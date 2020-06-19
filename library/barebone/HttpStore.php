<?php

namespace Application;

use Exception;
use Application\Session;
use Application\Http;

class HttpStore
{

	public static function capture(
	$key, $url, $method = 'GET', $params = array(), $headers = array()
	)
	{
		if (Session::has(sprintf('httpStore.%s', $key)))
		{
			return Session::all();
		}
		Session::set(
			sprintf('httpStore.%s', $key), array(
			'url' => $url,
			'method' => $method,
			'params' => $params,
			'headers' => $headers
		));

		return Session::all();
	}

	public static function replay($key)
	{
		if (!Session::has(sprintf('httpStore.%s', $key)))
		{
			return;
		}

		$sessionData = Session::get(sprintf('httpStore.%s', $key));
		switch (strtolower($sessionData['method']))
		{
			case 'get':
				$response = Http::get(
						$sessionData['url'], $sessionData['headers']
				);
				break;
			case 'post':
				$response = Http::post(
						$sessionData['url'], $sessionData['params'], $sessionData['headers']
				);
				break;
			default:
				$response = Http::get(
						$sessionData['url'], $sessionData['headers']
				);
		}
		Session::remove(sprintf('httpStore.%s', $key));
		return $response;
	}

}
