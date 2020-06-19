<?php

/**
 * Class and Function List:
 * Function list:
 * - __construct()
 * - respond()
 * - dependency()
 * - fs()
 * - disk()
 * - configurations()
 * - getrewritefile()
 * - googlepagespeedapiexist()
 * - googlepagespeed()
 * - getRamTotal()
 * - getRamFree()
 * - getDiskSize()
 * - Dbyte()
 * Classes list:
 * - Resource
 */
require_once sprintf(
	'%s%slibrary%sbootstrap.php', dirname(dirname(__DIR__)),
	DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR
);
Bootstrap::initialize('admin');


use Admin\Library\RewriteEngine;
use Application\Request;
use Application\Http;
use SebastianBergmann\Environment\Console;

class Resource
{

	protected $dependencies = array(
		array(
			'curl',
			'Communicating with servers via http/s protocol'
		),
		array(
			'mbstring',
			'Encoding/Decoding multibyte strings'
		),
		array(
			'mcrypt',
			'Encrypting/Decrypting strings'
		),
		array(
			'json',
			'Decoding API responses, encoding'
		),
	);

	protected $diskMeasure = array(
		"ram" => array( 1, 'RAM'), 
		"space" => array( 1, 'Disk Space'),
		"verison" => array( 1, 'Version'),
		"phpuser" => array( 1, 'PHP User'),
	);

	protected $fileSystem = array(
		array(
			'.htaccess',
			'../../.htaccess',
			'Rewrite rules, caching, server enhancements'
		),
		array(
			'storage',
			'../../storage/',
			'storage'
		),
		array(
			'extensions',
			'../../extensions/',
			'extensions'
		),
		array(
			'assets/dist',
			'../../assets/dist/',
			'storage'
		),
		array(
			'tmp',
			'../../tmp/',
			'tmp'
		)
	);

	protected $config = array(
		'license_key' => array(1 , 'Add API Key', 'license_key', 'license_key'),
		'domain' => array(1, 'Add Domain', 'general_settings', 'domain'),
		'offer_path' => array(1, 'Add offer path.', 'general_settings', 'offer_path'),
		'customer_service_number' => array(1, 'Add Customer Support Email', 'support_settings', 'support_number'),
		'customer_support_email' => array(1 , 'Add Customer Service Number', 'support_settings', 'support_email'),
		'app_prefix' => array(1 , 'Add app prefix')
	);

	protected $response;

	public function __construct()
	{
		
		if (in_array(@$_GET['method'], array(
				'dependency',
				'fs',
				'disk',
				'configurations',
				'cron_path',
				'generateRewriteFile',
				'getrewritefile',
				'settingsDevMode',
				'googlepagespeedapiexist',
				'googlepagespeed'
			)))
		{
			$this->{$_GET['method']}();
		}
		else
		{
			$this->response = array(
				'No method available'
			);
		}
	}

	public function respond()
	{
		if (!empty($this->response))
		{
			echo json_encode($this->response);
		}
	}

	protected function dependency()
	{
		$this->response = array();
		$this->response[] = array(
			'status' => version_compare(PHP_VERSION, '5.4.0') >= 0,
			'name' => 'Version',
			'details' => 'A minimum of 5.4+ is required',
			'installation' => 'http://php.net/downloads.php'
		);

		foreach ($this->dependencies as $dependency)
		{
			if(!strncmp($dependency[0], 'mcrypt', strlen('mcrypt')) && !extension_loaded($dependency[0])) {
				$this->response[] = array(
					'status' => true,
					'name' => 'Phpseclib',
					'details' => 'PHP Secure Communications Library',
					'installation' => sprintf('https://github.com/phpseclib/phpseclib')
				);
				continue;
			}

			$this->response[] = array(
				'status' => extension_loaded($dependency[0]) ? true : false,
				'name' => $dependency[0],
				'details' => $dependency[1],
				'installation' => sprintf('http://php.net/manual/en/%s.installation.php', $dependency[0])
			);
		}
	}

	protected function generateRewriteFile()
	{
		$res = RewriteEngine::generate(true);
		echo $res;
		die;
	}

	protected function getrewritefile()
	{
		$res = RewriteEngine::generate(true, true);
		if(strlen($res)) {
			$this->response = array(
				'status' => true,
				'details' => $res,
			);
		}
		else {
			$this->response[] = array(
				'status' => false,
				'error_message' => 'Something went worng, try again later',
			);
		}
		
	}

	protected function fs()
	{
		$this->response = array();
		foreach ($this->fileSystem as $file)
		{


			$this->response[] = array(
				'status' => is_writable($file[1]) ? true : false,
				'file' => $file[0],
				'details' => Request::getOfferPath().$file[0],
				// 'details' => $file[2]
			);
		}
	}

	protected function disk()
	{
		$this->response = array();

		foreach ($this->diskMeasure as $key => $item)
		{
			if(!strcmp($key, 'ram')) {

				$usedMemory = $this->Dbyte((int)$this->getRamTotal() -  (int)$this->getRamFree());
				$freeMemory = $this->Dbyte($this->getRamFree());
				$usedRamInPercentage = number_format(100 - (($this->getRamFree() / $this->getRamTotal()) * 100) , 2);
				$this->response[] = array(
					'status' => $usedRamInPercentage > 70 ? false : true,
					'details' => $item[1],
					'space' =>  sprintf("%.2f %%", (100 - $usedRamInPercentage)),
					'toolTips' => $usedRamInPercentage < 70 ? 'RAM performance looks fine.' : 'System consuming higher RAM, Have Attention.',
					'info' =>  str_replace("GB", "", $freeMemory) .  ' / ' . $this->Dbyte($this->getRamTotal())
				);
			}
			else if(!strcmp($key, 'space')) { 

				$diskinfo = $this->getDiskSize(PHP_OS == 'WINNT' ? 'C:' : '/');
				
				$freeSpace = str_replace("GB", "", $diskinfo['free']);
				$usedSpace = str_replace("GB", "", $diskinfo['used']);
				$totalMemory = str_replace("GB", "", $diskinfo['size']);
				$usedMemoryInPercent = number_format( ((int)$usedSpace / (int)$totalMemory ) * 100, 2);

				// In GB
				$this->response[] = array(
					'status' =>  $usedMemoryInPercent  > 80 ? false : true,
					'details' => $item[1],
					'space' => sprintf("%.2f %%", (100 - $usedMemoryInPercent)),
					'toolTips' => $usedMemoryInPercent < 90 ? 'Disk Space is ok.' : 'Disk Space is full, some function may not work properly.',
					'info' => $freeSpace . ' / ' . $diskinfo['size']
				);
			}else if(!strcmp($key, 'phpuser')) { 

                                $phpuser = 'Not Available';
                                if(function_exists('exec')) {
                                    $phpuser = exec('whoami');
                                }
				$this->response[] = array(
					'status' => true,
					'details' => 'PHP User',
					'space' => $phpuser,
					'toolTips' => 'PHPUser',
					'info' => 'Check Permission'
				);
			}
			else {
				preg_match("#^\d+(\.\d+)*#", PHP_VERSION, $match);
                                $phpuser = '';
                                if(function_exists('exec')) {
                                    $phpuser = "\n | PHP User: ".exec('whoami');
                                }
				$this->response[] = array(
					'status' => true,
					'details' => $item[1],
					'space' => $match[0],
					'toolTips' => 'Server PHP Version.',
					'info' => 'PHP Version '. $match[0].$phpuser
				);
			}
		}
	}

	protected function cron_path()
	{
		$this->response[] = realpath('../../process-delayed-upsell.php');
	}

	protected function configurations()
	{
		$file = file_get_contents('../../storage/admin/settings.data.json');
		$config = json_decode($file, true);

		$this->response = array();
		
		foreach ($config[0] as $key => $value)
		{	
			if (!is_bool($value) && !is_null($value) && isset($this->config[$key]) && empty($value))
			{
				$this->response[] = array(
					'key' => $this->config[$key][1],
					'importance' => $this->config[$key][0],
					'section' => $this->config[$key][2],
					'highlight' => $this->config[$key][3]
				);
			}
		}
	}

	protected function googlepagespeedapiexist()
	{
		$file = file_get_contents('../../storage/admin/settings.data.json');
		$config = json_decode($file, true);

		$this->response = array();

		if(empty($config[0]['domain'])){

			$this->response = array(
				'status' => false,
				'message' => 'Domain not found.'
			);
		}
		else {
			$this->response = array(
				'status' => true,
				'message' => 'Google PageSpeed is ready to perform.'
			);
		}
	}

	protected function googlepagespeed()
	{
		$loadFromCache = Request::get('loadChache');
		set_time_limit(0);

		$file = file_get_contents('../../storage/admin/settings.data.json');
		$config = json_decode($file, true);

		$mobileVersionExist = $config[0]['enable_mobile_version'];
		$mobileVersionOnly = $config[0]['mobile_version_only'];

		$this->response = array();

		if(empty($config[0]['domain'])){

			$this->response = array(
				'status' => false,
				'message' => 'Domain not found.'
			);
			return;
		}


		$offerUrl = sprintf('%s/', rtrim(Request::getOfferUrl(), '/'));

		if (strpos($offerUrl, 'localhost')) {

			$this->response = array(
				'status' => true,
				'speed' => array(
					array(
						"key"=>  "desktop",
						"value" => '90'
					),
					array(
						"key"=>  "mobile",
						"value" => '90'
					)
				),
				'message' => 'PageSpeed Found'
			);
			return;
		}

		// $offerUrl = "https://www.codeclouds.com";
		$speedData = array();

		$table = array(
            'name' => 'pagespeed',
            'attr' => array(
                'id' => 'integer',
                "desktop" => "string",
        		"mobile" => "string",
        		"last_modified" => "string"
            ),
        );

		//Check if data is already stored recently or not
		$cachePageSpeedfile = file_get_contents('../../storage/admin/pagespeed.json');
		$storedPageSpeedData = json_decode($cachePageSpeedfile, true);
	
		// Check the time diff is more than 2 minutes or not
		$timeDiff = 2; // In Minutes
		if($loadFromCache && !empty($storedPageSpeedData[0]) && ((time() - strtotime($storedPageSpeedData['last_modified'])) / 60) < $timeDiff) {

			unset($storedPageSpeedData['last_modified']);
			$speedData = $storedPageSpeedData;
		}
		else {

			foreach( [ "desktop", "mobile" ] as $strategy) {

				if(!$mobileVersionExist && !strncmp($strategy, 'mobile', strlen('mobile')))
					continue;

				if($mobileVersionOnly && !strncmp($strategy, 'desktop', strlen('desktop')))
					continue;

				$queryParams       = array(
					'url'        => $offerUrl,
					'strategy'   => $strategy,
				);
	
				$queryString = http_build_query($queryParams);
				$apiEndpoint = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed';
				$url         = sprintf(
					'%s/?%s', $apiEndpoint, $queryString
				);
				$response = Http::get($url);
				$response = json_decode($response, true);
	
				array_push($speedData, array(
					'key' => $strategy,
					'value' => (float)$response['lighthouseResult']['categories']['performance']['score'] * 100
				));
			}

			$storeIncache = $speedData;
			$storeIncache['last_modified'] = date("Y-m-d H:i:s");
			file_put_contents('../../storage/admin/pagespeed.json', json_encode($storeIncache));
		}		

		foreach( $speedData as $metrics){

			if(!strlen($metrics['value'])){
				$speedData[$metrics]['value'] = "0";
			}
		}
		
		if(!empty($speedData)) {
			$this->response = array(
				'status' => true,
				'speed' => $speedData,
				'message' => 'PageSpeed Found'
			);
		}
		else {
			$this->response = array(
				'status' => false,
				'message' => 'PageSpeed not found.'
			);
		}
		
	}

	private function getRamTotal()
    {
        $result = 0;
        if (PHP_OS == 'WINNT') {
            $lines = null;
            $matches = null;
            exec('wmic ComputerSystem get TotalPhysicalMemory /Value', $lines);
            if (preg_match('/^TotalPhysicalMemory\=(\d+)$/', $lines[2], $matches)) {
                $result = $matches[1];
            }
		}
		else if(PHP_OS == 'Darwin'){

			exec("/usr/sbin/system_profiler SPHardwareDataType", $hardwareInfo);

			$totalRam = explode(':', $hardwareInfo[12])[1];
			str_replace(' ', '', $totalRam);
			$result = $this->convertToBytes($totalRam);

		}
		else {
            $fh = fopen('/proc/meminfo', 'r');
            while ($line = fgets($fh)) {
                $pieces = array();
                if (preg_match('/^MemTotal:\s+(\d+)\skB$/', $line, $pieces)) {
                    $result = $pieces[1];
                    // KB to Bytes
                    $result = $result * 1024;
                    break;
                }
            }
            fclose($fh);
        }
        // KB RAM Total
        return $result;
    }

    private function getRamFree()
    {
        $result = 0;
        if (PHP_OS == 'WINNT') {
            $lines = null;
            $matches = null;
			exec('wmic OS get FreePhysicalMemory /Value', $lines);
			
            if (preg_match('/^FreePhysicalMemory\=(\d+)$/', $lines[2], $matches)) {
				
				$result = $matches[1] * 1024;
			}

        }
        else if(PHP_OS == 'Darwin')
        {
            exec("vm_stat", $res);
            $freeRam = explode(':', $res[1])[1];
            str_replace(' ', '', $freeRam);
            $result = $this->convertToBytes($freeRam);
        }
        else {
        $fh = fopen('/proc/meminfo', 'r');
        while ($line = fgets($fh)) {
            $pieces = array();
            if (preg_match('/^MemFree:\s+(\d+)\skB$/', $line, $pieces)) {
                // KB to Bytes
                $result = $pieces[1] * 1024;
                break;
            }
        }
        fclose($fh);
    }
    // KB RAM Total
    return $result;
    }
	
    private function Dbyte($Wert)
    {

        if ($Wert >= 1099511627776) {
            $Wert = round($Wert / 1099511627776, 1) . " TB";
        } elseif ($Wert >= 1073741824) {
            $Wert = round($Wert / 1073741824, 1) . " GB";
        } elseif ($Wert >= 1048576) {
            $Wert = round($Wert / 1048576, 1) . " MB";
        } elseif ($Wert >= 1024) {
            $Wert = round($Wert / 1024, 1) . " kB";
        } else {
            $Wert = round($Wert, 0) . " Bytes";
        }

        return $Wert;
	}

    private function getDiskSize($path = '/')
    {
        $result = array();
        $result['size'] = 0;
        $result['free'] = 0;
        $result['used'] = 0;

        if (PHP_OS == 'WINNT') {
            $lines = null;
			exec('wmic logicaldisk get FreeSpace^,Name^,Size /Value', $lines);
            foreach ($lines as $index => $line) {

                if ($line != "Name=$path") {
                    continue;
                }
                $result['free'] = $this->Dbyte(explode('=', $lines[$index - 1])[1]);
                $result['size'] = $this->Dbyte(explode('=', $lines[$index + 1])[1]);
                $result['used'] = ((int)$result['size'] - (int)$result['free']) . ' GB';
                break;
			}
		} 
		else {

            $lines = null;
            $cmdresult = exec(sprintf('df /P %s', $path), $lines);
            foreach ($lines as $index => $line) {
                if ($index != 1) {
                    continue;
                }
                $values = preg_split('/\s{1,}/', $line);
                $result['size'] = $this->Dbyte($values[1] * 1024);
                $result['free'] = $this->Dbyte($values[3] * 1024);
                $result['used'] = $this->Dbyte($values[2] * 1024);
                break;
            }

            if (is_null($cmdresult)) {

            	$diskFree = disk_free_space($path);
            	$totalDisk = disk_total_space($path);
            	$used = $totalDisk - $diskFree;
            	
            	$result['size'] = $this->Dbyte($totalDisk);

                $result['free'] = $this->Dbyte($diskFree);

                $result['used'] = $this->Dbyte($used);
            }
        }
        return $result;
	}
	
	private function convertToBytes($from) {

		$units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
		$number = substr($from, 0, -2);
		$suffix = strtoupper(substr($from,-2));
	
		//B or no suffix
		if(is_numeric(substr($suffix, 0, 1))) {
			return preg_replace('/[^\d]/', '', $from);
		}
	
		$exponent = array_flip($units)[$suffix] ? array_flip($units)[$suffix] : null;
		if($exponent === null) {
			return null;
		}
	
		return $number * (1024 ** $exponent);
	}
	
}

$res = new Resource();
header('Content-Type: text/json');
$res->respond();
