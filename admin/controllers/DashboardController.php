<?php

namespace Admin\Controller;

use Admin\Library\ResetSettings;
use Application\Helper\Alert;
use Application\Helper\Provider;
use Application\Helper\Security;
use Application\Model\Limelight;
use Application\Registry;
use Application\Request;
use Exception;
use Application\Hook\Crons;
use Admin\Controller\SettingsController;
use Application\Config;
use Application\Session;

class DashboardController
{
    public function allAlerts()
    {
        $this->checkEncryption();
        $this->checkDBCred();
        $this->getCronRunningStatus();
        return Alert::getData();
    }

    public function updateAlert()
    {
        $data = Request::form()->all();
        return Alert::updateData($data['id']);
    }

    public function quickLaunchers()
    {
        $extensionLaunchers           = Registry::extension('quick_launchers');
        $extensionLaunchersStructured = array();

        if (!empty($extensionLaunchers)) {
            foreach ($extensionLaunchers as $extension => $launchers) {
                foreach ($launchers as $launcher) {
                    $launcher['handler'] = Request::getOfferUrl() . AJAX_PATH . 'extensions/'
                    .
                    strtolower($extension)
                        .
                        '/'
                        .
                        $launcher['handler'];

                    $extensionLaunchersStructured[] = $launcher;
                }
            }
        }

        return $extensionLaunchersStructured;
    }

    public function resetSettings()
    {
        if (Request::form()->get('confirm') !== 'yes') {
            return;
        }
        $domain = Provider::removeSubDomain(
            trim(Request::getHttpHost(), '/')
        );

        try {
            ResetSettings::resetStorage();
            Security::registerDomain($domain);
        } catch (Exception $ex) {
            return array(
                'success'       => false,
                'data'          => array(),
                'error_message' => $ex->getMessage(),
            );
        }

        return array(
            'success' => true,
            'message' => 'Installation completed',
        );
    }

    public function registerDomain()
    {
        if (Request::form()->get('confirm') !== 'yes') {
            return;
        }
        $domain = Provider::removeSubDomain(
            trim(Request::getHttpHost(), '/')
        );
        Security::registerDomain($domain);
        return array(
            'success' => true,
            'message' => 'Notification disabled permanently.',
        );
    }

    public function getCronRunningStatus()
    {
        $isCronRunning  = true;
        $cronStatusFile = sprintf('%s%s.cron_running_status', STORAGE_DIR, DS);

        if (!file_exists($cronStatusFile)) {
            $isCronRunning = false;
        } elseif ((time() - filemtime($cronStatusFile)) > 30 * 60) {
            $isCronRunning = false;
        };

        $data = array(
            'identifier' => 'Cron Error',
            'text'       => "Cron job is not set, some of the important features of this CodeBase might not run properly",
            'type'       => 'error',
        );

        if (!$isCronRunning) {
            Alert::insertData($data);
        }else{
            Alert::removeData($data);
        }

        return array(
            'success'       => true,
            'isCronRunning' => $isCronRunning,
        );

    }

    public function isValidIP($ip){
        
        if (filter_var($ip, FILTER_VALIDATE_IP)){
            return true;
        } 
        return false;
    }

    public function checkDomainSwitch()
    {
        $domain = Provider::removeSubDomain(
            trim(Request::getHttpHost(), '/')
        );
        
        try{ 
            $settingsInstance = new SettingsController();
            $settingsData = $settingsInstance->all();
            if(
                !empty($domain) && 
                empty($this->isValidIP(trim(Request::getHttpHost(), '/'))) 
                &&  (!empty($settingsData['data'][0]['domain']) && 
                $domain !== $settingsData['data'][0]['domain'])
                )
            {
                
                $settingsData['data'][0]['encryption_key'] = "";
                $settingsData['data'][0]['gateway_switcher_id'] = "";
                $settingsInstance->removeWrongInstance($settingsData['data'][0]);
                
            }
            $this->checkDBCred();
            $this->checkEncryption();
        }catch(Exception $ex){

        }

        $domainSwitched = Security::isDomainChanged($domain);

        $data = array(
            'identifier' => 'Installation Incomplete!',
            'text'       => "This CodeBase didn't go through an installation process, which is crucial in terms of performance/security",
            'type'       => 'error',
        );

        if ($domainSwitched) {
            Alert::insertData($data);
        } else {
            Alert::removeData($data);
        }

        return array(
            'success'  => true,
            'switched' => $domainSwitched,
        );
    }
    
    public function checkPhpuserPermission()
    {
        $fileLocation = dirname(dirname(dirname(__FILE__))).DS."newfile.txt";
        $status = false;
        $msg = "Write permision is required";
        try{
            $myfile = fopen($fileLocation, "w");
            $txt = "Yes file is writable!!";
            $res = fwrite($myfile, $txt);
            if($res)
            {
                $status = true;
                $msg = 'Write permision is ok';
            }
            
        } catch (Exception $ex) {
            $status = false;
            $msg = $ex->getMessage();
        }
        
        return array(
            'success'  => $status,
            'message' => $msg,
        );
        
    }
    
    public function checkDevModeStatus()
    {
        $cron = new Crons();
        $cron->disableDevelopmentMode();

        return array(
            'success'  => true,
            'message' => 'Dev mode stauts verified.',
        );
    }
    
    public function updateTrackingID()
    {
        
        $maxTimeLimit = 12 * 60 * 60; // In seconds
        $fileName     = STORAGE_DIR . DS . '.remote_tracking';

        if (!file_exists($fileName)) {
            
            $res = $this->saveTrackingID();
            if($res)
             {
                 touch($fileName);
                 return array(
                    'success'  => true,
                    'message' => 'Tracking ID updated successfully.',
                 );
             }
             else{
                 return array(
                    'success'  => false,
                    'message' => 'Something went wrong.',
                 );
             }
        }

        $currentTime    = time();
        $fileModifiedAt = filemtime($fileName);
        
        if (($currentTime - $fileModifiedAt) > $maxTimeLimit) {
             $res = $this->saveTrackingID();
             
             if($res)
             {
                 touch($fileName);
                 return array(
                    'success'  => true,
                    'message' => 'Tracking ID updated successfully.',
                 );
             }
             else{
                 return array(
                    'success'  => false,
                    'message' => 'Something went wrong.',
                 );
             }
             
        }
        else{
            return array(
                'success'  => true,
                'message' => 'Already updated.',
             );
        }

    }
    
    private function saveTrackingID()
    {
        try
        {
            $settings = new SettingsController();
            $data = $settings->edit(1, true);
            return $data['success'];
        } catch (Exception $ex) {
            return false;
        }
    }

    
    public function purge()
    {
        $check = $this->checkExtensions('JsMinifier');

        if($check['success'] && $check['extensionCouponsActive']) {

            $minifire = new \Extension\JsMinifier\Compiler;

            $result = $minifire->execute();
        }
        else {
            $result = array(
                'success' => false,
                'error_message' => 'Js Minifier Extension not found.',
            );
        }

        return $result;
    }

    public function checkExtensions($extentionName = '')
    {
       
        $extentionName = strlen($extentionName) ? $extentionName : Request::get('extention');
       
        $result = array(
            'success' => true,
            'extensionCouponsActive' => false,
        );
        $extensions = Config::extensions();

        foreach ($extensions as $extension)
        {
            if ($extension['extension_slug'] !== $extentionName)
            {
                continue;
            }
            if ($extension['active'] === true)
            {
                $result['extensionCouponsActive'] = true;
                $result['active'] = $extension['active'];
            }
            break;
        }


        return $result;
    }

    public function checkPermission($slug)
    {
        $urlpermission = new UrlPermissionController();

        return $urlpermission->isValid($slug);
    }
    
    public function getDocumentation()
    {
        try{
         $mdDocs = array(
             'dashboard'=>'dashboard',
             'campaigns'=>'campaigns',
             'campaign-manager'=>'campaigns',
             'configurations'=>'configurations',
             'configuration-manager'=>'configurations',
             'settings'=>'settings',
             'cms'=>'cms',
             'crms'=>'crm',
             'crms-manager'=>'crm',
             'users'=>'users',
             'user-manager'=>'users',
             'pixels'=>'pixels',
             'pixel-setup'=>'pixels',
             'auto-responder'=>'auto-responder',
             'autoresponder-manager'=>'auto-responder',
             'scheduler'=>'scheduler',
             'cron-manager'=>'scheduler',
             'affiliates'=>'affiliates',
             'affiliate-manager'=>'affiliates',
             'crons'=>'scheduler',
             'extensions'=>'extensions',
             'extension-catalogue'=>'extensions',
             'routing'=>'mid-routing',
             'routing-manager'=>'mid-routing',
        );
         $slug = Request::form()->get('data')['slug'];
        // echo $mdDocs[$slug];die;
        if(empty($mdDocs[$slug]))
          throw new Exception('Not found');
       
        
        
        $url = 'https://framework.unify.to/unify_help_doc/sections/'.
                $mdDocs[$slug].'.md';
        $docs = file_get_contents($url);
        $docs = $this->relatedMDChunk($slug,$docs);
        if(empty($docs))
            throw new Exception('Not found');
       
         return array(
                'success' => true,
                'data' =>  $docs,
            );
         
        }catch(Exception $ex){
            return array(
                'success' => false,
                'data' => '**404 Not found.**',
                'error_message' => $ex->getMessage(),
            );
        }
    }
    
    private function relatedMDChunk($slug,$param)
    {
        if(!in_array($slug, array('campaign-manager',
            'configuration-manager','crms-manager',
            'user-manager','pixel-setup',
            'autoresponder-manager','affiliate-manager','cron-manager', 'extension-catalogue','routing-manager')))
                return $param;
        
        $keywords = preg_split("/##\sAdd/i", $param);
        return '## Add'.end($keywords);
    }


    public function getFeedbackDetails() {

        $userType = Session::get('userType');

        if (!strcmp($userType, 'developer')) {
            $userTypeCaption = 'developer';
            $userIdentification = Session::get('googleEmail');
        }
        else {
            $userTypeCaption = UsersController::userTypeToString($userType);
            $userIdentification = Session::get('username');
        }

        try {
            $ip = Request::getClientIP();
            $response = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"));
            $details = array(
                $response->city, $response->region, $response->country
            );
            $details = array_filter($details);
        }
        catch(Exception $e) {
            //$details = null;
        }

        $response = array(
            'UserType' => $userTypeCaption,
            'Identification' => $userIdentification,
            'OfferUrl' => Request::getOfferUrl(),
            'IP' => Request::getClientIP(),
            'Location' => !empty($details) ? implode(',', $details) : 'Not Found'
        );

        return array(
            'success' => true,
            'message' => $response
        );
    }

    public function getFrameworkVersion() {

        $currentVersion = Registry::system('systemConstants.version');
        
        $file = "https://framework.unify.to/extension-lists/framework.version.json";
        if (file_get_contents(__FILE__) && ini_get('allow_url_fopen')) {
            
            $availableVersion = file_get_contents($file);
        }
        else if (function_exists('curl_version')) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $file);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $availableVersion = curl_exec($curl);
            curl_close($curl);
        }
        else {
            return array(
                'success' => false,
                'error_message' => "Enable the CURL or allow_url_fopen in php.ini to get the version update."
            );
        }

        $availableVersion = json_decode($availableVersion, TRUE);

        return array(
            'success' => true,
            'data' => array(
                'currentVersion' => $currentVersion,
                'availableVersion' => $availableVersion['version'],
                'isNewVersionAvailable' => version_compare($currentVersion, $availableVersion['version']) < 0
            )
        );
    }
    public function checkDBCred(){
           $settingsData = config::settings();
            $settings = new SettingsController();          
            Request::form()->set('db_host',$settingsData['db_host']);
            Request::form()->set('db_username',$settingsData['db_username']);
            Request::form()->set('db_password',$settingsData['db_password']);
            Request::form()->set('db_name',$settingsData['db_name']);
            
            $dbVerify = $settings->checkDatabaseConnection()['data']['db_verify'];
            
        if(!empty($dbVerify) || 
                (!$this->checkExtensions('DataCapture')['active'] && 
                !$this->checkExtensions('DelayedTransactions')['active'])){
            Alert::removeData(array(
                 'identifier' => 'Database error',
                 'type'       => 'error',
                 'text' => 'Please check your database credential'
                 ));
        }
        if(empty($dbVerify) && ($this->checkExtensions('DataCapture')['active'] ||
                $this->checkExtensions('DelayedTransactions')['active'])){
            Alert::insertData(
                array(
                 'identifier' => 'Database error',
                 'type'       => 'error',
                 'text' => 'Please check your database credential'
                 )
            );
        }
    }
    public function checkEncryption() { 
        $activeRequiredExtensions = false;
        if($this->checkExtensions('DataCapture')['active']){
            $activeRequiredExtensions = true;
        }
        $settings = config::settings();
        if(!empty($settings['encryption_key']) || !$activeRequiredExtensions){
             Alert::removeData(array(
                 'identifier' => 'Encryption Key Required',
                 'type'       => 'error',
                 'text' => 'Encryption key is not found. '
                    . 'Some important feature will not work properly due to blank encryption key'
                 ));
            
        }
        if($activeRequiredExtensions && empty($settings['encryption_key'])){
            Alert::insertData(
                array(
                'identifier' => 'Encryption Key',
                    'identifier' => 'Encryption Key Required',
                    'text' => 'Encryption key is not found. '
                    . 'Some important feature will not work properly due to blank encryption key',
                     'type'       => 'error',
                )
            );
        }
       
    }
}
