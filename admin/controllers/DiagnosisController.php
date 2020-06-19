<?php

namespace Admin\Controller;

use Exception;
use Lazer\Classes\Database;
use Lazer\Classes\Helpers\Validate;
use Lazer\Classes\LazerException;
use Application\Request;
use Application\Registry;
use Application\Http;

class DiagnosisController
{

    private $extensions;

    public function __construct()
    {
        $this->extensions = array(
            array(
                'heading' => 'Asynchronous Prospect',
                'slug' => 'AsyncProspect',
                'type' => 'optional',
                'error_msg' => 'Not installed, make the landing form submit faster by installing the extension',
                'inactive_msg' => 'Not active, make the landing form submit faster by activating the extension',
                'success_msg' => 'Installed'
            ),
            array(
                'heading' => 'Js Minifier',
                'slug' => 'JsMinifier',
                'type' => 'required',
                'error_msg' => 'Not installed, might result to a slow loading pages',
                'inactive_msg' => 'Not active, might result to a slow loading pages',
                'success_msg' => 'Installed'
            ),
            array(
                'heading' => 'Asynchronous Split',
                'slug' => 'AsyncSplit',
                'type' => 'required',
                'error_msg' => 'Extension is not installed, might result to very slow checkout.Please download/activate the extension',
                'inactive_msg' => 'Extension is not active, might result to very slow checkout.Please activate the extension',
                'success_msg' => 'Installed'
            ),
            array(
                'heading' => 'Developer Tools',
                'slug' => 'DeveloperTools',
                'type' => 'required',
                'error_msg' => 'Not installed, install/activate the extension to keep the codebase up to date with enhancements and security patches',
                'inactive_msg' => 'Not active, install/activate the extension to keep the codebase up to date with enhancements and security patches',
                'success_msg' => 'Installed'
            )
        );

        $this->configurations = array(
            'force_preserve_gateway' => array(
                'message' => 'Force gateway on but Preserve gateway is set to false.',
                'type' => 'optional'
            ),
            'link_with_parent' => array(
                'message' => 'In Split charge, Link with parent settings is set to false.',
                'type' => 'optional'
            ),
            'accept_prepaid_cards' => array(
                'message' => 'No prepaid campaign associated with selected Campaigns ',
                'type' => 'required'
            ),
            'force_gateway' => array(
                'message' => 'Force Gateway enabled.',
                'type' => 'optional'
            ),
            'force_split' => array(
                'message' => 'Split is forced with parent gateway',
                'type' => 'optional'
            ),
            'konnektive_utility_package' => array(
                'message' => 'Konnektive Utility Package is turned off',
                'type' => 'required'
            )
        );

        $this->settings = array(
            'force_https' => array(
                'heading' => 'Force HTTPS',
                'error_msg' => 'Not enabled',
                'success_msg' => 'Enabled',
                'type' => 'required'
            ),
            'development_mode' => array(
                'heading' => 'Development Mode',
                'error_msg' => 'Off',
                'success_msg' => 'On',
                'type' => 'optional'
            ),
            'mobile_path' => array(
                'heading' => 'Mobile Version Path',
                'error_msg' => 'Directory not exists',
                'success_msg' => 'Directory exists',
                'type' => 'required'
            )
        );

        $this->pixels = array(
            'prepaid' => array(
                'message' => 'Fire Pixel For Prepaid is set to on.',
                'type' => 'optional'
            )
        );

        $this->backupDirectory = array(
            'heading' => 'Backup Directory',
            'success_msg' => 'Directory exists and writable',
            'error_msg_write' => 'Directory exists but unable to write',
            'error_msg_create' => 'Unable to create the directory',
            'type' => 'optional'
        );

        $this->frameworkDirectory = array(
            'heading' => 'Framework Directory',
            'success_msg' => 'Framework is writable',
            'error_msg_write' => 'Permission is required for ',
            'type' => 'required'
        );

        $this->debugContents = array(
            'heading' => 'Debug Script',
            'success_msg' => 'Debug Script is secure',
            'error_msg' => 'Debug Script is not secure',
            'type' => 'required'
        );

        $this->debugCache = array(
            'heading' => 'Caching',
            'success_msg' => 'No Caching',
            'error_msg' => 'Caching is present',
            'curl_error_msg' => 'Cache checking is failed',
            'type' => 'required'
        );
        
        $this->tagChecker = array(
            'heading' => 'Tags in pages',
            'success_msg' => 'Tags not missing',
            'error_msg' => 'Tags missing in',
            'type' => 'required'
        );
        
        $this->apiEndpoint = array(
            'heading' => 'API Endpoint',
            'success_msg' => 'Connection is ok',
            'error_msg' => 'Connection failed',
            'type' => 'required'
        );
    }

    public function extensions()
    {
        try
        {

            foreach ($this->extensions as $key => $extension)
            {
                $data = Database::table('extensions')
                                ->where('extension_slug', '=', $extension['slug'])
                                ->findAll()->asArray();
                $this->extensions[$key]['status'] = !empty($data) ? (($data[0]['active']) ? true : false) : false;
                $this->extensions[$key]['message'] = !empty($data) ? (($data[0]['active']) ? $extension['success_msg'] : $extension['inactive_msg']) : $extension['error_msg'];
            }

            return array(
                'success' => true,
                'data' => $this->extensions
            );
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

    public function configurations()
    {
        try
        {
            $data = Database::table('configurations')
                            ->orderBy('id', 'desc')
                            ->findAll()->asArray();
            $returnData = [];
            if (!empty($data))
            {
                foreach ($data as $key => $val)
                {
                    $configurationName = $val['configuration_label'] . "(#" . $val['id'] . ")";
                    /* for preserve gateway */
                    if (!empty($val['force_gateway_id']) && !$val['preserve_gateway'])
                    {
                        $returnData[] = array(
                            'heading' => $configurationName,
                            'message' => $this->configurations['force_preserve_gateway']['message'],
                            'type' => $this->configurations['force_preserve_gateway']['type']
                        );
                    }
                    /* for Split charge */
                    if (!empty($val['split_charge']) && empty($val['link_with_parent']))
                    {
                        $returnData[] = array(
                            'heading' => $configurationName,
                            'message' => $this->configurations['link_with_parent']['message'],
                            'type' => $this->configurations['link_with_parent']['type']
                        );
                    }
                    /* for force gateway */
                    if (!empty($val['force_gateway_id']))
                    {
                        $returnData[] = array(
                            'heading' => $configurationName,
                            'message' => $this->configurations['force_gateway']['message'],
                            'type' => $this->configurations['force_gateway']['type']
                        );
                    }
                    /* for force split */
                    if (!empty($val['split_charge']) && !empty($val['split_force_parent_gateway']))
                    {
                        $returnData[] = array(
                            'heading' => $configurationName,
                            'message' => $this->configurations['force_split']['message'],
                            'type' => $this->configurations['force_split']['type']
                        );
                    }
                    /* for Prepaid check on configuration */
                    if ($val['accept_prepaid_cards'])
                    {
                        $campaignIds = [];
                        $splitCampaignIds = [];
                        $allCampaignIds = [];
                        if (!empty($val['campaign_ids']))
                        {
                            $campaignIds = json_decode($val['campaign_ids'], 1);
                        }

                        if (!empty($val['split_campaign_ids']))
                        {
                            $splitCampaignIds = json_decode($val['split_campaign_ids'], 1);
                        }

                        $allCampaignIds = array_merge($campaignIds, $splitCampaignIds);

                        if (!empty($allCampaignIds))
                        {
                            $dataCamp = Database::table('campaigns');
                            $errorCamp = array();
                            foreach ($allCampaignIds as $allCampaignId)
                            {
                                $data_count = $dataCamp->where('id', '=', $allCampaignId)
                                                ->where('prepaid_campaign_id', '=', '0')
                                                ->findAll()->asArray();
                                if (!empty($data_count))
                                {
                                    array_push($errorCamp, $allCampaignId);
                                }
                            }

                            if (!empty($errorCamp))
                            {
                                $returnData[] = array(
                                    'heading' => $configurationName,
                                    'message' => $this->configurations['accept_prepaid_cards']['message'] . ' ' . implode(',', $errorCamp),
                                    'type' => $this->configurations['accept_prepaid_cards']['type']
                                );
                            }
                        }
                    }
                }

                /* for konnektive utility package */
               
                $flag = 0;
                $installedKonnektive = 0;
                $konnektiveCrm = 0;
                $installedExtensions = Database::table('extensions')
                                ->findAll()->asArray();
                $crmType = Database::table('crms')
                                ->findAll()->asArray();
                
                foreach($crmType as $crm){
                    if($crm['crm_type'] == 'konnektive'){
                        $konnektiveCrm = 1;
                    }
                }
               
                if (!empty($installedExtensions) && $konnektiveCrm == 1)
                {
                    foreach ($installedExtensions as $extensionKey => $extensionVal)
                    {
                        if ($extensionVal['extension_slug'] == 'KonnektiveUtilPack')
                        {
                            $installedKonnektive = 1;
                            if (empty($extensionVal['active']))
                            {
                                $flag = 1;
                            }
                        }
                       
                    }
                    if ($flag == 1 || $installedKonnektive == 0)
                    {
                        $returnData[] = array(
                            'heading' => 'Konnektive Utility Package',
                            'message' => $this->configurations['konnektive_utility_package']['message'],
                            'type' => $this->configurations['konnektive_utility_package']['type']
                        );
                    }
                }
            }

            return array(
                'success' => true,
                'data' => $returnData
            );
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

    public function settings()
    {
        try
        {
            $data = Database::table('settings')->where('id', '=', 1)->findAll()->asArray();
            $returnData = [];
            if (!empty($data))
            {
                foreach ($data as $key => $val)
                {
                    /* for Force HTTPS */
                    $returnData[] = array(
                        'heading' => $this->settings['force_https']['heading'],
                        'message' => $val['force_https'] ? $this->settings['force_https']['success_msg'] : $this->settings['force_https']['error_msg'],
                        'status' => $val['force_https'],
                        'type' => $this->settings['force_https']['type']);

                    /* for Development Mode */
                    $returnData[] = array(
                        'heading' => $this->settings['development_mode']['heading'],
                        'message' => $val['development_mode'] ? $this->settings['development_mode']['success_msg'] : $this->settings['development_mode']['error_msg'],
                        'status' => $val['development_mode'] ? false : true,
                        'type' => $this->settings['development_mode']['type']);

                    /* for Mobile version folder check */
                    if (!empty($val['mobile_path']))
                    {
                        $mobileDirectory = APP_DIR . DS . $val['mobile_path'];

                        $returnData[] = array(
                            'heading' => $this->settings['mobile_path']['heading'],
                            'message' => is_dir($mobileDirectory) ? $this->settings['mobile_path']['success_msg'] : $this->settings['mobile_path']['error_msg'],
                            'status' => is_dir($mobileDirectory),
                            'type' => $this->settings['mobile_path']['type']);
                    }
                }
            }

            return array(
                'success' => true,
                'data' => $returnData
            );
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

    public function pixels()
    {
        try
        {
            $data = Database::table('pixels')
                            ->orderBy('id', 'desc')
                            ->findAll()->asArray();
            $returnData = [];
            if (!empty($data))
            {
                foreach ($data as $key => $val)
                {
                    $pixelName = $val['pixel_name'] . "(#" . $val['id'] . ")";
                    /* for prepaid check */
                    if ($val['prepaid'])
                    {
                        $returnData[] = array(
                            'heading' => $pixelName,
                            'message' => $this->pixels['prepaid']['message'],
                            'type' => $this->pixels['prepaid']['type']);
                    }
                }
            }

            return array(
                'success' => true,
                'data' => $returnData
            );
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

    public function backupDirectory()
    {
        try
        {
            $storageBackupPath = LAZER_BACKUP_PATH;

            $returnData = array(
                'heading' => $this->backupDirectory['heading'],
                'message' => $this->backupDirectory['success_msg'],
                'status' => true,
                'type' => $this->backupDirectory['type']);

            if (is_dir($storageBackupPath))
            {
                if (!is_writable($storageBackupPath))
                {
                    if (!chmod($storageBackupPath, 0755))
                    {
                        $returnData['message'] = $this->backupDirectory['error_msg_write'];
                        $returnData['status'] = false;
                    }
                }
            }
            else
            {
                if (!mkdir($storageBackupPath, 0755))
                {
                    $returnData['message'] = $this->backupDirectory['error_msg_create'];
                    $returnData['status'] = false;
                }
            }
            return array(
                'success' => true,
                'data' => array($returnData)
            );
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

    public function frameworkDirectory()
    {
        try
        {
            $adminPath = ADMIN_DIR;
            $assestsPath = BASE_DIR . DS . 'assets';
            $libraryPath = LIB_DIR;
            $langsPath = LANG_DIR;

            $checkedDir = array(
                $adminPath,
                $assestsPath,
                $libraryPath,
                $langsPath
            );

            $errorDir = array();

            $returnData = array(
                'heading' => $this->frameworkDirectory['heading'],
                'message' => $this->frameworkDirectory['success_msg'],
                'status' => true,
                'type' => $this->frameworkDirectory['type']);


            foreach ($checkedDir as $dir)
            {
                if (!is_writable($dir))
                {
                    $folderName = explode(DS, $dir);
                    $folderName = end($folderName);
                    array_push($errorDir, $folderName);
                }
            }

            if (!empty($errorDir))
            {
                $returnData['message'] = $this->frameworkDirectory['error_msg_write'] . ' ' . implode(', ', $errorDir);
                $returnData['status'] = false;
            }
            else
            {
                $returnData['message'] = $this->frameworkDirectory['success_msg'];
                $returnData['status'] = true;
            }
            return array(
                'success' => true,
                'data' => array($returnData)
            );
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

    public function debugContents()
    {
        try
        {
            $returnData = array(
                'heading' => $this->debugContents['heading'],
                'message' => $this->debugContents['success_msg'],
                'status' => true,
                'type' => $this->debugContents['type']);

            $response = Http::get(
                            Request::getOfferUrl() . 'test.php'
            );

            if (!empty($response['curlError']))
            {
                $returnData['message'] = $this->debugContents['error_msg'];
                $returnData['status'] = false;
            }
            else
            {
                if (strlen($response))
                {
                    $returnData['message'] = $this->debugContents['error_msg'];
                    $returnData['status'] = false;
                }
                else
                {
                    $returnData['message'] = $this->debugContents['success_msg'];
                    $returnData['status'] = true;
                }
            }
            return array(
                'success' => true,
                'data' => array($returnData)
            );
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
    
    public function tagChecker()
    {
        try
        {
            $returnData = array(
                'heading' => $this->tagChecker['heading'],
                'message' => $this->tagChecker['success_msg'],
                'status' => true,
                'type' => $this->tagChecker['type']);
            
            $missedDir = array();
            $folderForScan = array('desktop','mobile');
            
            foreach ($folderForScan as $device)
            {  
                $folderName = dirname(dirname(__DIR__)). DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . $device;
                $scanned_directory = array_diff(scandir($folderName), array('..', '.'));
                
                foreach($scanned_directory as $files){
                    $filename = $folderName. DIRECTORY_SEPARATOR .$files;                
                    if($files != '.htaccess' && is_file($filename)){
                        $fileBody = file_get_contents($filename);
                        if(!strpos($fileBody, 'perform_body_tag_open_actions')){
                            array_push($missedDir,$files.' (open tag:'.$device.')');
                        }
                        if(!strpos($fileBody, 'perform_body_tag_close_actions')){
                            array_push($missedDir,$files.' (close tag:'.$device.')');
                        }
                    }
                }
                
            }
        
            if (!empty($missedDir))
            {
                $returnData['message'] = $this->tagChecker['error_msg']. ' ' . implode(',', $missedDir);
                $returnData['status'] = false;
            }
            else
            {
                $returnData['message'] = $this->tagChecker['success_msg'];
                $returnData['status'] = true;
            }
           
            return array(
                'success' => true,
                'data' => array($returnData)
            );
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

    public function debugCache()
    {
        try
        {
            $returnData = array(
                'heading' => $this->debugCache['heading'],
                'message' => $this->debugCache['success_msg'],
                'status' => true,
                'type' => $this->debugCache['type']);

            $url = Request::getOfferUrl() . 'index.php';

            $curlOptions = array(
                CURLOPT_HEADER => true
            );
            
            $content = Http::get($url, array(), $curlOptions);
            
            if (!empty($content['curlError'])) {
                $returnData['message'] = $this->debugCache['curl_error_msg'];
                $returnData['status'] = false;
            } 
            else{
                $headerArray = $this->getHeaderArray($content);            
                $expiryDate = strtotime($headerArray['Expires']);
                $currentDate = strtotime('now');
                if ($expiryDate > $currentDate)
                {
                    $returnData['message'] = $this->debugCache['error_msg'];
                    $returnData['status'] = false;
                }
                else
                {
                    $returnData['message'] = $this->debugCache['success_msg'];
                    $returnData['status'] = true;
                }
            }
            return array(
                'success' => true,
                'data' => array($returnData)
            );
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

    public function getHeaderArray($content)
    {
        $headers = array();
        $header_text = substr($content, 0, strpos($content, "\r\n\r\n"));
        foreach (explode("\r\n", $header_text) as $i => $line)
        {
            if ($i === 0)
            {
                $headers['http_code'] = $line;
            }
            else
            {
                list ($key, $value) = explode(': ', $line);
                $headers[$key] = $value;
            }
        }
        return $headers;
    }
    
    public function apiEndpoint()
    {
        try
        {
            $returnData = array(
                'heading' => $this->apiEndpoint['heading'],
                'message' => $this->apiEndpoint['success_msg'],
                'status' => true,
                'type' => $this->apiEndpoint['type']);
            
            $data = Database::table('crms')
                             ->findAll()->asArray();
            
            $apiError = array();
            
            foreach (array_keys($data) as $crmId) {
                $urlParts = parse_url($data[$crmId]['endpoint']);
                if (!empty($urlParts['scheme'])) {
                    $data[$crmId]['endpoint'] = str_replace(
                        sprintf(
                            '%s://', $urlParts['scheme']
                        ), '', $data[$crmId]['endpoint']
                    );
                }
                $data[$crmId]['endpoint'] = sprintf(
                    'https://%s', $data[$crmId]['endpoint']
                );
                
                $curl = curl_init();

                curl_setopt_array($curl, array(
                  CURLOPT_URL => $data[$crmId]['endpoint'],
                  CURLOPT_SSL_VERIFYHOST => false,
                  CURLOPT_SSL_VERIFYPEER => false,
                  CURLOPT_RETURNTRANSFER => true,
                  CURLOPT_ENCODING => "",
                  CURLOPT_MAXREDIRS => 10,
                  CURLOPT_TIMEOUT => 30,
                  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                  CURLOPT_CUSTOMREQUEST => "GET",
                  CURLOPT_HTTPHEADER => array(
                    "cache-control: no-cache",
                   
                  ),
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);
                
                curl_close($curl);
                
                if($err){
                    array_push($apiError,$data[$crmId]['endpoint']);
                }
                
            }

            if (!empty($apiError))
            {
                $returnData['message'] = $this->apiEndpoint['error_msg'] . ' ' . implode(', ', $apiError);
                $returnData['status'] = false;
            }
            else
            {
                $returnData['message'] = $this->apiEndpoint['success_msg'];
                $returnData['status'] = true;
            }
            return array(
                'success' => true,
                'data' => array($returnData)
            );
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

}
