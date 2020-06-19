<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Admin\Library;

use Application\Session;
use Application\Request;
use Application\Config;
use Google_Client;
use Google_Service_Plus;

class GoogleLogin
{

	private $redirect_url;
	protected $client;
	protected $plus;

	public function __construct()
	{
		$this->redirect_url = Request::getBaseUrl() . '/index.php';
		$this->client = new Google_Client();
		$this->client->setClientId(Config::settings('google_client_id'));
		$this->client->setClientSecret(Config::settings('google_secret_key'));
		$this->client->setRedirectUri($this->redirect_url);
		$this->client->setScopes('email');
		$this->plus = new Google_Service_Plus($this->client);
		$guzzleClient = new \GuzzleHttp\Client(array('curl' => array(CURLOPT_SSL_VERIFYPEER => false,),));
		$this->client->setHttpClient($guzzleClient);
	}

	public function authenticateAccesstoken($code = null)
	{
		$this->client->authenticate($code);
		return $this->client->getAccessToken();
	}

	public function setAccessToken($accesstoken = null)
	{
		$this->client->setAccessToken($accesstoken);
		$whiteListedDomain = Config::settings('whitelisted_domains');
		$whiteListedDomainarr = explode("\n",$whiteListedDomain);
		$me = $this->plus->people->get('me');
		$email  = $me['emails'][0]['value'];
		$emailFragments = explode('@', $email);
		$domainName = array_pop($emailFragments);
		if (!in_array($domainName, $whiteListedDomainarr))
		{
			Session::clear();
			return false;
		}
		Session::set('googleEmail', $email);
		Session::set('userType', 'developer'); 
		// Get User data
		Session::set('id', $me['id']);
		Session::set('name', $me['displayName']);
		Session::set('profile_image_url', $me['image']['url']);
		Session::set('cover_image_url', $me['cover']['coverPhoto']['url']);
		Session::set('profile_url', $me['url']);
		return false;
	}

	public function getAuthUrl()
	{
		return $this->client->createAuthUrl();
	}

	public function logout()
	{
		Session::clear();
		header('location:index.php');
	}

}
