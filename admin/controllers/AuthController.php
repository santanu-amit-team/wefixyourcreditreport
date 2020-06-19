<?php

namespace Admin\Controller;

use Exception;
use Lazer\Classes\Database;
use Lazer\Classes\Helpers\Validate;
use Lazer\Classes\LazerException;
use Application\Request;
use Application\Session;
use function GuzzleHttp\json_decode;
use function GuzzleHttp\json_encode;

class AuthController
{

	private $access_code;

	public function __construct()
	{
		$get_code = file_get_contents(LAZER_DATA_PATH  . '.passwords' . DS . '.master');
		$this->access_code = $get_code;
	}

	public function validateLogin()
	{
		try
		{
			$get_access_code = Request::form()->get('access_code', '');

			if ($get_access_code == $this->access_code)
			{
				Session::set('loginSession', 1);
				return array(
					'success' => true,
					'data' => Session::get('loginSession')
				);
			}
			else
			{
				return array(
					'success' => false,
					'data' => array(),
					'error_message' => 'Invalid access code',
				);
			}
		}
		catch (Exception $ex)
		{
			return array(
				'success' => false,
				'data' => array(),
				'error_message' => $ex->getMessage(),
			);
		}
	}

	public function checkLogin()
	{
		if((Session::has('access_token') && Session::has('googleEmail')) || (Session::has('userType') && Session::get('userType') == 3))
		{
			Session::set('loginSession', 1);
		}

		return array(
			'success' => (Session::has('loginSession')) ? true : false,
			'data' => Session::get('loginSession')
		);
	}


	public function isDocsValid()
	{
		$token = Request::form()->get('token');
		
		if(empty($token)) {
			return array(
				'success' => false,
				'message' => 'Unauthorized access not allowed.'
			);
		}

		$authToken = explode('|', base64_decode($token));

		if(count($authToken) < 2) {
			return array(
				'success' => false,
				'message' => 'Not Valid Token.'
			);
		}
		
		if(file_exists(session_save_path() . DS . 'sess_' . $authToken[0])) {
			session_id($authToken[0]);
			$originalToken = Session::has('DocAccessToken') ? Session::get('DocAccessToken') : null;
			
			if(!strncmp($authToken[1], $originalToken, strlen($originalToken))) {
				//Success
				return array(
					'success' => true,
					'message' => 'Access Granted.'
				);
			}
			else {
				return array(
					'success' => false,
					'message' => 'Unauthorized access not allowed.'
				);
			}
		}
		else {
			return array(
				'success' => false,
				'message' => 'Unauthorized access not allowed.'
			);
		}
	}
}