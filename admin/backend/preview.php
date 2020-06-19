<?php
namespace Admin\Backend;

$library_dir = dirname(__FILE__) . '/../../library';
require_once $library_dir . DIRECTORY_SEPARATOR . 'bootstrap.php';

\Bootstrap::initialize('admin');

use Lazer\Classes\Database as Lazer;
use Admin\Library\Auth;


class Preview
{

	protected $cms;
	protected static $request;

	public function __construct()
	{
        $this->cms = Lazer::table('cms');
        self::$request = $_REQUEST;
        if(!Auth::isLoginAuthorized() || !isset(self::$request['id'])) {
            exit('URL Not Found.');
        }

		$content = $this->getContent(self::$request['id']);
		if($content) {
			echo $this->loadTemplate($content);
        }
        else {
            exit('Page Not Found');
        }
    }
    
    protected function loadTemplate($content)
	{
		$tpl = file_get_contents('cms.tpl');

		$string = str_replace(
			array(
			'[[title]]',
			'[[body]]',
			), array(
			$content->content_name,
			$content->content_body,
			), $tpl
		);

		$settings = Lazer::table('settings')->find(1);

		$string = preg_replace_callback('/\[\[([^\]]+)\]\]/', function($match) use ($settings)
		{
                        $match[1] = trim($match[1]);
			return !empty($settings->{$match[1]}) ? $settings->{$match[1]} : '';

		}, $string);

		echo $string;
		exit;
	}

	protected function getContent($contentID)
	{
		try
		{
			return $this->cms->find($contentID);
		}
		catch (Exception $ex)
		{
			return;
		}
	}
}

$cmsPreview = new Preview();
