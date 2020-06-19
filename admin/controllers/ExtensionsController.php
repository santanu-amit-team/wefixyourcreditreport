<?php

namespace Admin\Controller;

use Application\Config;
use Application\Helper\Security;
use Application\Helper\Provider;
use Application\Http;
use Application\Registry;
use Application\Request;
use Exception;
use Lazer\Classes\Database;
use Lazer\Classes\Helpers\Validate;
use Lazer\Classes\LazerException;
use PclZip;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Admin\Controller\CronController;
use SebastianBergmann\Environment\Console;
use function GuzzleHttp\json_decode;

class ExtensionsController
{
    const FILLER = 'filler.jpg';
    private $table, $manageTable, $dbFileName, $extensionSlug, $extension, $extensionList, $extensionInputs, $extensionDir, $remoteUrl, $remoteListUrl, $remoteDownloadUrl, $remoteDownloadUrl2;

    public function __construct($args)
    {

        $this->accessor          = PropertyAccess::createPropertyAccessor();
        $this->remoteUrl         = Registry::system('systemConstants.REMOTE_URL');
        $this->remoteListUrl     = Registry::system('systemConstants.REMOTE_LIST_URL');
        $this->remoteChangelogUrl = Registry::system('systemConstants.REMOTE_LIST_CHANGELOG_URL');
        $this->remoteDownloadUrl = Registry::system('systemConstants.REMOTE_DOWNLOAD_URL');
        $this->extensionSlug     = empty($args[0]) ? null : $args[0];
        $this->dbFileName        = LAZER_DATA_PATH . DS . 'extensionsConfig.data.json';
        $this->createInputs();
        $this->createManageTable();
        $this->extensionDir = dirname(__DIR__) . DS . 'lazerdb';
        $this->table        = array(
            'name' => 'extensions',
            'attr' => array(
                'id'             => 'integer',
                'extension_slug' => 'string',
                'extension_name' => 'string',
                'extension_des'  => 'string',
                'active'         => 'boolean',
                'installed'      => 'boolean',
                'version'        => 'string',
                'edit_status'    => 'integer',
                'protected'      => 'boolean',
                'cover'          => 'string',
                'category'       => 'string',
                'author'         => 'string',
            ),
        );

        try
        {
            Validate::table($this->table['name'])->exists();
        } catch (LazerException $ex) {
            Database::create(
                $this->table['name'], $this->table['attr']
            );
        }
    }

    private function extensionList($slug = null)
    {
        $extensions = json_decode(
            Http::get(
                $this->remoteListUrl
            ), true
        );
        $extension = array();
        if (!is_array($extensions)) {
            $extensions = array();
        }

        if ($slug === null) {
            return $extensions;
        } else {
            foreach ($extensions as $key => $value) {
                if ($value['extension_slug'] == $slug) {
                    $extension = $value;
                    break;
                }
            }
            if (empty($extension)) {
                throw new Exception("Extension Not Found in List");
            }
            return $extension;
        }
    }

    public function allExtensions()
    {
        $extensions = $this->extensionList();
        try
        {
            $installedExtensions = Database::table($this->table['name'])
                ->findAll()->asArray();
        } catch (Exception $ex) {
            $installedExtensions = array();
        }

        $result   = array();
        $coverURL = Registry::system('systemConstants.REMOTE_URL') . 'covers/new/';
        foreach ($extensions as $extension) {
            $data = array(
                'extension_slug' => $this->accessor->getValue($extension, '[extension_slug]'),
                'extension_name' => $this->accessor->getValue($extension, '[extension_name]'),
                'extension_des'  => strip_tags($this->accessor->getValue($extension, '[extension_des]')),
                'extension_des_full'  => $this->accessor->getValue($extension, '[extension_des]'),
                'active'         => null,
                'id'             => null,
                'installed'      => false,
                'version'        => $this->accessor->getValue($extension, '[version]'),
                'edit_status'    => null,
                'cover'          => $this->accessor->getValue($extension, '[cover]')
                ? ($coverURL . $this->accessor->getValue($extension, '[cover]'))
                : ($coverURL . self::FILLER),
                'protected'      => (boolean) (!empty($extension['protected'])),
                'cover_name'    => $this->accessor->getValue($extension, '[cover]'),
                'category'    => $this->accessor->getValue($extension, '[category]'),
                'author'    => $this->accessor->getValue($extension, '[author]')
            );
            if ($this->extensionExists($this->accessor->getValue($extension, '[extension_slug]')) === false) {
                array_push($result, $data);
                continue;
            }
            $index = -1;
            for ($ii = 0, $len = count($installedExtensions); $ii < $len; $ii++) {
                if (
                    $installedExtensions[$ii]['extension_slug'] == $this->accessor->getValue($extension, '[extension_slug]')
                ) {
                    $index = $ii;
                    break;
                }
            }
            if ($index === -1) {
                array_push($result, $data);
                continue;
            }
            $data['installed']   = $installedExtensions[$index]['installed'];
            $data['version']     = $installedExtensions[$index]['version'];
            $data['active']      = $installedExtensions[$index]['active'];
            $data['id']          = $installedExtensions[$index]['id'];
            $data['edit_status'] = $installedExtensions[$index]['edit_status'];
            $data['protected']   = (boolean) (!empty($installedExtensions[$index]['protected']));

            array_splice($installedExtensions, $index, 1);
            array_push($result, $data);
        }
        return array(
            'success' => true,
            'data'    => $result,
            'message' => 'Extensions data successfully fetched.',
        );
    }
    public function installedExtensions()
    {
        try
        {
            $installedExtensions = Database::table($this->table['name'])
                ->findAll()->asArray();
        } catch (Exception $ex) {
            $installedExtensions = array();
        }
        $result = array();
        $data   = array();
        if (is_array($installedExtensions) && !empty($installedExtensions)) {
            foreach ($installedExtensions as $extension) {
                $options = null;
                if ($extension['edit_status'] != 0) {
                    if ($extension['active'] === true) {
                        $inputs = $this->getInputs(
                            $this->accessor->getValue($extension, '[extension_slug]'), $type = 'options'
                        );
                        if (!$inputs == false) {
                            $options = $inputs;
                        }
                    }
                }
                $data = array(
                    'extension_slug' => $this->accessor->getValue($extension, '[extension_slug]'),
                    'extension_name' => $this->accessor->getValue($extension, '[extension_name]'),
                    'extension_des'  => $this->accessor->getValue($extension, '[extension_des]'),
                    'active'         => $this->accessor->getValue($extension, '[active]'),
                    'id'             => $this->accessor->getValue($extension, '[id]'),
                    'installed'      => $this->accessor->getValue($extension, '[installed]'),
                    'version'        => $this->accessor->getValue($extension, '[version]'),
                    'edit_status'    => $this->accessor->getValue($extension, '[edit_status]'),
                    'options'        => $options,
                    'protected'      => (boolean) (!empty($extension['protected'])),
                    'cover'          => $this->accessor->getValue($extension, '[cover]'),
                    'category'    => $this->accessor->getValue($extension, '[category]'),
                    'author'    => $this->accessor->getValue($extension, '[author]')
                );
                array_push($result, $data);
            }
        }
        return array(
            'success' => true,
            'data'    => $result,
        );
    }

    private function extensionExists($extensionSlug)
    {
        $fileConfig = sprintf(
            '%s%s%s%sconfig.php', EXTENSION_DIR, DS, $extensionSlug, DS
        );
        $file = EXTENSION_DIR . DS . $extensionSlug;
        if (file_exists($fileConfig)) {
            $fileConfig = require $fileConfig;
            $customHtml = $this->accessor->getValue($fileConfig, '[custom_html]');
            if ($this->accessor->getValue($fileConfig, '[settings]') || $customHtml['enable']) {
                return 1;
            }

            return 2;
        }
        return false;
    }

    public function getInputs($extensionSlug, $type)
    {
        try
        {
            $filePath = sprintf(
                '%s%s%s%sconfig.php', EXTENSION_DIR, DS, $extensionSlug, DS
            );
            if (!file_exists($filePath)) {
                return false;
            }
            $filePath = require $filePath;
            if (!array_key_exists($type, $filePath)) {
                return false;
            }
            $inputs = $filePath[$type];
            if (empty($inputs)) {
                return false;
            }
            return $inputs;
        } catch (Exception $ex) {
            return array(
                'success'       => false,
                'data'          => array(),
                'error_message' => $ex->getMessage(),
            );
        }
    }

    public static function getAttr($extensionSlug, $type)
    {
        try
        {
            $filePath = sprintf(
                '%s%s%s%sconfig.php', EXTENSION_DIR, DS, $extensionSlug, DS
            );
            if (!file_exists($filePath)) {
                return false;
            }
            $filePath = require $filePath;
            if (!array_key_exists($type, $filePath)) {
                return false;
            }
            $inputs = $filePath[$type];
            if (empty($inputs)) {
                return false;
            }
            return $inputs;
        } catch (Exception $ex) {
            return array(
                'success'       => false,
                'data'          => array(),
                'error_message' => $ex->getMessage(),
            );
        }
    }

    public function get($extensionSlug)
    {
        try
        {
            $row = Database::table($this->table['name'])
                ->where('extension_slug', '=', $extensionSlug)
                ->find()->asArray();
            $data = array();

            return array(
                'success' => true,
                'data'    => $row,
            );
        } catch (Exception $ex) {
            return array(
                'success'       => false,
                'data'          => array(),
                'error_message' => $ex->getMessage(),
            );
        }
    }

    private function setFormRequest($formData)
    {
        foreach ($formData as $key => $value) {
            Request::form()->set($key, $value);
        }
    }

    public function downloadExtension($extensionSlug)
    {
        set_time_limit(0);
//        if(!strncmp($extensionSlug, 'DeveloperTools', strlen('DeveloperTools'))) {
//
//            $doUpdateFramework = $this->updateFramework();
//            if(!$doUpdateFramework) {
//                return array(
//                    'success'       => false,
//                    'error_message' => 'Framework update failed.',
//                );
//            }
//        }
//
//
//        if(!strncmp($extensionSlug, 'ThirdPartyVendor', strlen('ThirdPartyVendor'))) {
//
//            $doUpdateFrameworkVendor = $this->updateFrameworkVendor();
//            if(!$doUpdateFramework) {
//                return array(
//                    'success'       => false,
//                    'error_message' => 'Framework vendor update failed.',
//                );
//            }
//        }
            
        $flag          = Request::form()->get('flag');
        $systemRequest = Request::form()->has('systemRequest') ? Request::form()->get('systemRequest') : false;
        $domain        = trim(Request::getHttpHost(), '/');
        $params        = array(
            'extension_slug' => $extensionSlug,
            'license_key'    => Config::settings('license_key'),
            'auth_key'       => Config::settings('unify_authentication_key'),
            'domain'         => Provider::removeSubDomain($domain),
        );

        $downloadUrl = $this->remoteDownloadUrl
        .
        '?'
        .
        http_build_query($params);

        $extensionData = $this->get($extensionSlug);
        if(sizeof($extensionData['data']) > 0 && ($flag == "download")){
            return array(
                'success'       => false,
                'error_message' => "Extension already exists",
            ); 
        }

        $url  = sprintf($this->remoteUrl . '%s.zip', $extensionSlug);
        $path = EXTENSION_DIR . DS . $extensionSlug . '.zip';

        $fp = fopen($path, 'w');
        try
        {
            $response = Http::download($downloadUrl, $fp);
            if (!empty($response['httpCode']) && $response['httpCode'] == 401) {
                throw new Exception(
                    'Coudn’t verify license key, '
                    . 'please enter a valid license key in settings to download this extension.'
                );
            } else if (!empty($response['httpCode']) && $response['httpCode'] == 503) {
                throw new Exception('Service Unavailable.');
            } else if ($response !== true && !empty($response['httpCode']) && $response['httpCode'] == 404) {
                throw new Exception('Sorry, couldn’t locate the extension.');
            } else if ($response !== true) {
                throw new Exception($response['error']);
            }
            $archive = new PclZip($path);
            if (!$archive->extract(PCLZIP_OPT_PATH, EXTENSION_DIR, PCLZIP_OPT_REPLACE_NEWER)) {
                throw new Exception('Unzip proccess failed');
            }
            fclose($fp);
            unlink($path);
            $configExists = $this->extensionExists($extensionSlug);

            if ($configExists == false) {
                return array(
                    'success'       => false,
                    'error_message' => 'Extension configuration file error.',
                );
            }
            $extension = $this->extensionList($extensionSlug);
            // $action = $flag == 'download' ? 'add' : 'update';
            $id = null;
            $coverURL = Registry::system('systemConstants.REMOTE_URL') . 'covers/new/';

            $formData = array(
                        'extension_slug' => $this->accessor->getValue($extension, '[extension_slug]'),
                        'extension_name' => $this->accessor->getValue($extension, '[extension_name]'),
                        'extension_des'  => strip_tags($this->accessor->getValue($extension, '[extension_des]')),
                        'active'         => false,
                        'installed'      => true,
                        'version'        => $this->accessor->getValue($extension, '[version]'),
                        'edit_status'    => ($configExists == 2) ? 2 : 0,
                        'protected'      => (boolean) (!empty($extension['protected'])),
                        'cover'          => is_null($this->accessor->getValue($extension, '[cover]')) ? self::FILLER : $this->accessor->getValue($extension, '[cover]'),
                        'category'    => $this->accessor->getValue($extension, '[category]'),
                        'author'    => $this->accessor->getValue($extension, '[author]')
                    );
            
            if ($flag == 'update') {
                
                if (is_array($extensionData['data']) && !empty($extensionData['data'])) {
                    $data     = $extensionData['data'][0];
                    $id     = $data['id'];
                    $formData['active'] = $systemRequest ? $data['active'] : false;
                    $formData['installed'] = $data['installed'];
                }    
            }
            $this->setFormRequest($formData);
            $actionResponse = ($flag === 'update') ? $this->edit($id) : $this->add();
           
            if (!$actionResponse['success']) {
                $this->deleteFolder(EXTENSION_DIR . DS . $extensionSlug);
                throw new Exception("Download failed, please try again later.");  
            }
            $actionResponse['data']['cover'] = $this->accessor->getValue($actionResponse['data'], '[cover]')
                ? ($coverURL . $this->accessor->getValue($actionResponse['data'], '[cover]'))
                : ($coverURL . self::FILLER);


            $message = "The extension has " . ($flag == "update" ? "updated" : "installed") . ", go ahead and activate it.";
//            if(!strncmp($extensionSlug, 'DeveloperTools', strlen('DeveloperTools'))) {
//
//                $message .= "Great! Framework has been upgraded to a newer version";
//            }

            return array(
                'success'       => true,
                'config_exists' => $configExists,
                'version'       => $extension['version'],
                'data'          => $actionResponse['data'],
                'message'       => $message,
            );
        } catch (Exception $ex) {
            fclose($fp);
            unlink($path);

            if(strpos($ex->getMessage(), 'license')) {
                return array(
                    'success'       => false,
                    'type' => 'warning-popup',
                    'error_message' => $ex->getMessage(),
                );
            }

            return array(
                'success'       => false,
                'error_message' => $ex->getMessage(),
            );
        }
    }
    
    public function getExtensionsVersion() 
    {
        try
        {
            $data = Http::get($this->remoteListUrl);
           
            if(!empty($data['curlError']))
            {
                return array(
                    'success' => false,
                    'data' => '',
                );
            }
            $extensions = json_decode($data, true);            
            return array(
                'success' => true,
                'data' => $extensions,
            );
            
        } catch (Exception $ex) {
            return array(
                'success' => false,
                'data' => '',
            );
        }
    
    }

    public function add()
    {
        try
        {
            $row = Database::table($this->table['name'])
                ->where(
                    'extension_slug', '=', Request::form()->get('extension_slug')
                )
                ->find();
            if (empty($row->id)) {
                $row = Database::table($this->table['name']);
            }
            $data = array();
            foreach ($this->table['attr'] as $key => $type) {
                if ($key === 'id') {
                    continue;
                }
                $data[$key] = $row->{$key} = $this->filterInput($key);
            }
            if ($this->isValidData($row)) {
                $row->save();
                $data['id'] = $row->id;
                return array(
                    'success' => true,
                    'data'    => $data,
                );
            }
        } catch (Exception $ex) {
            return array(
                'success'       => false,
                'data'          => $data,
                'error_message' => $ex->getMessage(),
            );
        }
    }

    private function registryCreate($extensionSlug, $status)
    {
        $jsCompilationRequired = false;
        $jsMinifier            = array();

        $configFile = sprintf(
            '%s%s%s%sconfig.php', EXTENSION_DIR, DS, $extensionSlug, DS
        );

        if (!file_exists($configFile)) {
            throw new Exception('Config File is not exists');
        }
        $data         = require $configFile;
        $registryFile = sprintf('%s%sregistry.json', STORAGE_DIR, DS);
        $registry     = array();

        $registryElements = array(
            'hooks', 'routes', 'crons', 'scripts', 'quick_launchers',
        );

        if (file_exists($registryFile)) {
            $registry = json_decode(file_get_contents($registryFile), 1);
        }

        foreach ($registryElements as $registryElement) {
            if (!array_key_exists($registryElement, $registry)) {
                $registry[$registryElement] = array();
            }
        }
        foreach ($registryElements as $registryElement) {
            if ($status == 'activate') {
                if (
                    !empty($data[$registryElement]) &&
                    is_array($data[$registryElement])
                ) {
                    if ($registryElement == 'scripts') {
                        $jsCompilationRequired = true;
                    }
                    
                    $registry[$registryElement][$extensionSlug] = $data[$registryElement];
                    //$this->updateCronHelper($registryElement, $registry, $registryFile, $extensionSlug);
                    
                    // This if block is written to insert the cron into crons.data.json
                    if($registryElement == 'crons') {
                        $cronUi = new CronController();
                        foreach($data[$registryElement] as $cron) {
                            $res = $cronUi->import($cron,$extensionSlug);
                        }
                    }
                    
                }
            } 
            else {
                // This if block is written to changestatus the cron into crons.data.json
                if($registryElement == 'crons') {

                    $cronUi = new CronController();

                    $cronIds = CronController::getCronIdByExtensionSlug($extensionSlug);
                    if(!empty($cronIds)){
                        foreach($cronIds as $cron){
                            $cronUi->status($cron,false);
                        }
                    }
                    
                }

                if (
                    !empty($registry[$registryElement][$extensionSlug])
                ) {
                    if ($registryElement == 'scripts') {
                        $jsCompilationRequired = true;
                    }
                    unset($registry[$registryElement][$extensionSlug]);
                }
            }
        }
        $putContent = file_put_contents($registryFile, json_encode($registry), LOCK_EX);

        if ($putContent != false) {

            if ($jsCompilationRequired == true) {
                Registry::reloadExtension();

                $jsMinifier = Database::table($this->table['name'])
                    ->where(
                        'extension_slug', '=', 'JsMinifier'
                    )
                    ->find()->asArray();

                if (is_array($jsMinifier) && empty($jsMinifier)) {
                    return;
                }
                if ($jsMinifier[0]['active'] === false) {
                    return;
                }

                $compiler = new \Extension\JsMinifier\Compiler();
                $result   = $compiler->execute();

                if ($result['success'] == true) {
                    $jsCompilationRequired = false;
                }

            }

        }

    }
    
    private function updateCronHelper($registryElement, $registry, $registryFile, $extensionSlug)
    {
        $registryFileForUI = sprintf('%s%scron_registry.json', STORAGE_DIR, DS);
        if (!file_exists($registryFileForUI)) {
            throw new Exception('Config File is not exists');
        }
        $registryCrons = array();
        if ($registryElement == 'crons') {
            if (file_exists($registryFile)) {
                $registryUI = json_decode(file_get_contents($registryFileForUI), 1);
            }

            if(!empty($registryUI[$extensionSlug]))
            {
                foreach ($registryUI[$extensionSlug] as $ukey => $uiValue)
                {
                    array_push($registryCrons, $uiValue['handler']);
                }
            }
            

            if(!empty($registryCrons))
            {
                foreach ($registry[$registryElement][$extensionSlug] as $regKey => $regValue)
                {
                    if(!in_array($regValue['handler'], $registryCrons))
                    {
                        $registryUI[$extensionSlug][count($registryCrons)] = $regValue;
                    }
                }
            }
            else{
                $registryUI[$extensionSlug] = $registry[$registryElement][$extensionSlug];
            }
            file_put_contents($registryFileForUI, json_encode($registryUI), LOCK_EX);
        }
    }

    private function performActions($data)
    {
        $status     = ($data['active'] == true) ? 'activate' : 'deactivate';
        $configFile = sprintf(
            '%s%s%s%sconfig.php', EXTENSION_DIR, DS, $data['extension_slug'], DS
        );

        if (file_exists($configFile)) {
            $configFile = require $configFile;

            $this->registryCreate($data['extension_slug'], $status);

            if (!empty($configFile['actions'][$status])) {
                $meta = explode('@', $configFile['actions'][$status]);
                call_user_func(array((new $meta[0]()), $meta[1]));
            }

            return true;
        }
    }

    /*
     * @params $localCall is decider for whether edit is called from UI or inside from framework.
     * Validation of License key will by pass if edit function call from inside of framework. 
     */
    public function edit($id = '', $localCall = false)
    {
        $isDeactivating = !Request::form()->get('active');
        if(!$localCall) {
            $validLicense = Security::isValidLicenseKey(Config::settings('domain'), Config::settings('license_key'), Config::settings('unify_authentication_key'));

            if (!$isDeactivating && !$validLicense) {
                return array(
                    'success'       => false,
                    'error_message' => 'Unable to process. Your license key has expired.',
                    'type'          => 'alert',
                );
            } 
        }

        try
        {
            $row  = Database::table($this->table['name'])->find($id);
            $data = array();
            foreach ($this->table['attr'] as $key => $type) {
                if ($key === 'id') {
                    continue;
                }
                $data[$key] = $row->{$key} = $this->filterInput($key);
            }

            if ($this->isValidData($row)) {
                $status = $this->performActions($data);
                if ($status) {
                    $row->save();
                    $data['id'] = $row->id;
                    if ($data['active'] == false) {
                        $data['options'] = null;
                    } else {
                        $inputs = $this->getInputs($data['extension_slug'], $type = 'options');
                        if (!$inputs == false) {
                            $data['options'] = $inputs;
                        }
                    }

                    return array(
                        'success' => true,
                        'data'    => $data,
                    );
                }
            }
        } catch (Exception $ex) {
            return array(
                'success'       => false,
                'error_message' => $ex->getMessage(),
                'type'          => 'alert',
            );
        }
    }

    private function deleteFolder($path)
    {
        if (is_dir($path) === true) {
            $files = array_diff(scandir($path), array('.', '..'));

            foreach ($files as $file) {
                $this->deleteFolder(realpath($path) . '/' . $file);
            }

            return rmdir($path);
        } else if (is_file($path) === true) {
            return unlink($path);
        }

        return false;
    }

    public function uninstallExtension($extensionSlug)
    {
        try
        {
            // if($extensionSlug == 'DeveloperTools'){
            //     return array(
            //     'success' => false,
            //     'message' => 'Something went wrong in delete',
            //     'data' => $data
            // );
            // }
            $extension = $this->get($extensionSlug);
            $repoData = $this->extensionList($extensionSlug);
            $data = [];
            if (is_array($extension['data']) && !empty($extension['data'])) {
                $extension = $extension['data'][0];
                $this->registryCreate($extensionSlug, 'deactivate');
            }

            $coverURL = Registry::system('systemConstants.REMOTE_URL') . 'covers/new/';
            $extensionData = array(
                'extension_slug' => $this->accessor->getValue($repoData, '[extension_slug]'),
                'extension_name' => $this->accessor->getValue($repoData, '[extension_name]'),
                'extension_des'  => $this->accessor->getValue($repoData, '[extension_des]'),
                'active'         => null,
                'id'             => null,
                'installed'      => false,
                'version'        => $this->accessor->getValue($repoData, '[version]'),
                'edit_status'    => null,
                'cover'          => $this->accessor->getValue($repoData, '[cover]')
                ? ($coverURL . $this->accessor->getValue($repoData, '[cover]'))
                : ($coverURL . self::FILLER),
                'protected'      => (boolean) (!empty($repoData['protected'])),
                'category'    => $this->accessor->getValue($repoData, '[category]'),
                'author'    => $this->accessor->getValue($repoData, '[author]')
            );

            if ($this->extensionExists($extensionSlug) != 2) {
                $data = json_decode(file_get_contents($this->dbFileName), 1);
                $row  = array();
                if (!empty($data[$extensionSlug])) {
                    $row = $data[$extensionSlug];
                }
                if (!empty($row)) {
                    unset($data[$extensionSlug]);
                }
                file_put_contents($this->dbFileName, json_encode($data), LOCK_EX);
            }

            $deleteData = Database::table($this->table['name'])
                ->where('extension_slug', '=', $extensionSlug)
                ->find()
                ->delete();

            $deletedFolder = $this->deleteFolder(EXTENSION_DIR . DS . $extensionSlug);

            if(!empty($deleteData) && !empty($deletedFolder)){
                
                // Remove the crons from crons.data.json
                $cronUi = new CronController();
                $cronUi->deleteCronBySlugName($extensionSlug);

                return array(
                    'success' => true,
                    'message' => 'Uninstalled',
                    'data' => $extensionData
                );
            }
            return array(
                'success' => false,
                'message' => 'Something went wrong in delete',
                'data' => $extensionData
            );

            
        } catch (Exception $ex) {
            return array(
                'success'       => false,
                'error_message' => $ex->getMessage(),
            );
        }
    }

    /* Manager extension */

    private function createInputs()
    {
        try
        {
            $filePath = sprintf(
                '%s%s%s%sconfig.php', EXTENSION_DIR, DS, $this->extensionSlug, DS
            );
            if (!file_exists($filePath)) {
                $this->extensionInputs == array();
                return false;
            } else {
                $filePath              = require $filePath;
                $this->extensionInputs = $this->accessor->getValue($filePath, '[settings]');
                return $this->extensionInputs;
            }
        } catch (Exception $ex) {
            return array(
                'success'       => false,
                'data'          => array(),
                'error_message' => $ex->getMessage(),
            );
        }
    }

    public function getFirst($extensionSlug)
    {
        $flag = '';
        if (!file_exists($this->dbFileName)) {
            return false;
        }
        $data = json_decode(file_get_contents($this->dbFileName), 1);
        $row  = array();
        if (!empty($data[$extensionSlug])) {
            $row  = $data[$extensionSlug];
            $flag = 'Edited';
        }
        if (empty($row)) {
            $inputsAttr = $this->manageTable['attr'];
            foreach ($inputsAttr as $key => $type) {
                switch ($type) {
                    case 'integer':
                        $row[$key] = '';
                        break;
                    case 'boolean':
                        $row[$key] = false;
                        break;
                    default:
                        $row[$key] = '';
                }
            }
            $flag = 'Not edited';
        }
        $data['flag'] = $flag;
        $data['row']  = $row;
        return $data;
    }

    public function getExtension($extensionSlug)
    {
        try
        {
            $count         = 0;
            $extensionData = array();
            $extensionData = Database::table($this->table['name'])
                ->where(
                    'extension_slug', '=', $this->extensionSlug
                )
                ->find()->asArray();
            if (is_array($extensionData) && !empty($extensionData)) {
                $extensionData = $extensionData[0];
            }
            
            $customHtmlContent = '';
            $customHtmlDetails = $this->getInputs($this->extensionSlug, 'custom_html');
            if(!empty($customHtmlDetails['enable']))
            {
                $customHtmlContentPath    = EXTENSION_DIR . DS . $this->extensionSlug . DS . $customHtmlDetails['template_name'];
                $customHtmlContent = file_get_contents($customHtmlContentPath);
            }
          
            if (!empty($extensionData)) {
                $schema = $this->getInputs($this->extensionSlug, 'settings');
                $datas = $this->getFirst($this->extensionSlug);

                if(!empty($customHtmlContent)) {
                    return array(
                        'success'   => true,
                        'extension' => $extensionData,
                        'schema'    => $schema,
                        'datas'     => $datas['row'],
                        'flag'      => $datas['flag'],
                        'custom_html'    => $customHtmlContent,
                        'template_js'    => $customHtmlDetails['template_js']
                    );
                }
            }
            throw new Exception(http_response_code(404));
        } catch (Exception $ex) {
            return array(
                'success'       => false,
                'error_message' => $ex->getMessage(),
            );
        }
    }

    private function createManageTable()
    {
        if (!file_exists($this->dbFileName)) {
            file_put_contents($this->dbFileName, '{}', LOCK_EX);
        }
        if (empty($this->extensionInputs)) {
            $this->manageTable = array(
                'name' => '',
                'attr' => array(),
            );
            return;
        }
        $this->manageTable['name'] = $this->extensionSlug;
        $inputs                    = $this->extensionInputs;
        foreach ($inputs as $key => $val) {
            $attr[$val['key']] = $val['type'];
        }
        $this->manageTable['attr'] = $attr;
    }

    private function saveActionCall()
    {
        $actions = $this->getInputs($this->extensionSlug, $type = 'actions');
        if (is_array($actions) && !empty($actions) && array_key_exists('save', $actions)) {
            $meta = explode('@', $actions['save']);
            call_user_func(array((new $meta[0]()), $meta[1]));
        }
    }

    public function addManageData()
    {
        try {

            $this->saveActionCall();
            $data = json_decode(file_get_contents($this->dbFileName), 1);
            $row  = array();
            foreach ($this->manageTable['attr'] as $key => $type) {
                $row[$key] = $this->filterInputManage($key);
            }
            $row = array_merge($row, Request::form()->all());
            
            $data[$this->manageTable['name']] = $row;
            if ($this->isValidData($row)) {
                file_put_contents($this->dbFileName, json_encode($data), LOCK_EX);
                $activeData = $this->get($this->extensionSlug);
                $editData   = $activeData['data'][0];
                if (!$editData['edit_status'] == 0) {
                    return array(
                        'success' => true,
                        'data'    => $editData,
                        'message' => 'All changes are saved successfully',
                    );
                }
                $formData = array(
                    'extension_slug' => $this->accessor->getValue($editData, '[extension_slug]'),
                    'extension_name' => $this->accessor->getValue($editData, '[extension_name]'),
                    'extension_des'  => $this->accessor->getValue($editData, '[extension_des]'),
                    'active'         => $this->accessor->getValue($editData, '[active]'),
                    'installed'      => $this->accessor->getValue($editData, '[installed]'),
                    'version'        => $this->accessor->getValue($editData, '[version]'),
                    'protected'      => (boolean) (!empty($editData['protected'])),
                    'edit_status'    => 1,
                    'cover'          => $this->accessor->getValue($editData, '[cover]'),
                    'category'       => $this->accessor->getValue($editData, '[category]'),
                    'author'         => $this->accessor->getValue($editData, '[author]')
                );
                $this->setFormRequest($formData);
                $editResponse = $this->edit($editData['id']);
                if ($editResponse['success'] == 1) {
                    return array(
                        'success' => true,
                        'data'    => $editResponse['data'],
                        'message' => 'All changes are saved successfully',
                    );
                } else {
                    throw new Exception('Error in extension data update');
                }
            }
            return array(
                'success'       => false,
                'data'          => Request::form()->all(),
                'error_message' => $ex->getMessage(),
            );
        } catch (Exception $ex) {
            return array(
                'success'       => false,
                'data'          => Request::form()->all(),
                'error_message' => $ex->getMessage(),
            );
        }
    }

    public function editManageData($extensionSlug)
    {
        try
        {
            $this->saveActionCall();
            $data = json_decode(file_get_contents($this->dbFileName), 1);
            $row  = array();
            if (!empty($data[$this->extensionSlug])) {
                $row = $data[$this->extensionSlug];
            }
            
            $row = array_merge($row, Request::form()->all());
            
            if (!empty($row)) {
                foreach ($this->manageTable['attr'] as $key => $type) {
                    $row[$key] = $this->filterInputManage($key);
                }
            }
            $data[$this->extensionSlug] = $row;

            if ($this->isValidData($row)) {
                file_put_contents($this->dbFileName, json_encode($data), LOCK_EX);
                $activeData = $this->get($this->extensionSlug);
                $editData   = $activeData['data'][0];

                if (!$editData['edit_status'] == 0) {
                    return array(
                        'success' => true,
                        'data'    => $editData,
                        'message' => 'All changes are saved successfully',
                    );
                }
                $formData = array(
                    'extension_slug' => $this->accessor->getValue($editData, '[extension_slug]'),
                    'extension_name' => $this->accessor->getValue($editData, '[extension_name]'),
                    'extension_des'  => $this->accessor->getValue($editData, '[extension_des]'),
                    'active'         => $this->accessor->getValue($editData, '[active]'),
                    'installed'      => $this->accessor->getValue($editData, '[installed]'),
                    'version'        => $this->accessor->getValue($editData, '[version]'),
                    'edit_status'    => 1,
                    'protected'      => (boolean) (!empty($editData['protected'])),
                    'cover'          => $this->accessor->getValue($editData, '[cover]'),
                    'category'       => $this->accessor->getValue($editData, '[category]'),
                    'author'         => $this->accessor->getValue($editData, '[author]')

                );

                $this->setFormRequest($formData);

                $editResponse = $this->edit($editData['id']);
                if ($editResponse['success'] == 1) {
                    return array(
                        'success' => true,
                        'data'    => $editResponse['data'],
                        'message' => 'All changes are saved successfully',
                    );
                } else {
                    throw new Exception('Error in extension data update');
                }
            }
        } catch (Exception $ex) {
            return array(
                'success'       => false,
                'data'          => Request::form()->all(),
                'error_message' => $ex->getMessage(),
            );
        }
    }

    /**/

    private function filterInput($key)
    {
        switch ($this->table['attr'][$key]) {
            case 'integer':
                return Request::form()->getInt($key, 0);
            case 'boolean':
                return (boolean) Request::form()->get($key, false);
            default:
                return Request::form()->get($key, '');
        }
    }

    private function filterInputManage($key)
    {
        switch ($this->manageTable['attr'][$key]) {
            case 'integer':
                return Request::form()->getInt($key, 0);
            case 'boolean':
                return (boolean) Request::form()->get($key, false);
            default:
                return Request::form()->get($key, '');
        }
    }

    private function isValidData($data)
    {
        return true;
    }
    
    public function getCustomJsFiles()
    {
        $customHtmlDetails = $this->getInputs($this->extensionSlug, 'custom_html');
        return array(
                'success' => true,
                'data'    => $customHtmlDetails['template_js'],
                'message' => 'All changes are saved successfully',
            );
    }

    public function changelog($slug)
    {
        try {
            $fileConfig = '';
            if($slug == 'framework'){
                $fileConfig = sprintf('%s%s%s.md', $this->remoteChangelogUrl, '/', 'readme');
            }
            else{
                $fileConfig = sprintf('%s%s%s-readme.md', $this->remoteChangelogUrl, '/', $slug);
                // $fileConfig = sprintf('%s%s%s.md', $this->remoteChangelogUrl, DS, 'readme');
            }
            $changelogData =  Http::get( $fileConfig );
            if (empty($changelogData)) {
                throw new Exception("Changelog Not Found");
            }
            return array(
                'success' => true,
                'data' => $changelogData,
            );
            
        } catch (Exception $ex) {
            return array(
                'success'       => false,
                'error_message' => $ex->getMessage(),
            );
        }
    }

    // public function extensionListUpdate()
    // {
    //     $categories = [
    //         'security' => [
    //             'KonnektiveUtilPack', 'LimelightUtilPack', 'CloudFlare'
    //         ],
    //         'utility'  => [
    //             'AsyncProspect',  'DataCapture', 'AsyncSplit', 'TrafficLoadBalancer', 'DeveloperTools', 'DelayedTransactions', 'InputMask', 'CbUtilityPackage', 'Boilerplate', 'Affiliates', 'Rotators', 'MidsRouting', 'Afs', 'BinRouting', 'LenderLBP', 'UrlRotator', 'DietMasterWeb', 'BlacklistService', 'BinManager', 'EmanageUtilPack', 'ProductManagement', 'Cascade', 'LeadPost', 'PrepaidForce', 'TemplateSwitcher'
    //         ],
    //         'crm'      => [
    //             'Limelight3DS', 'LimelightTracking', 'Gratify', 
    //         ],
    //         'automation' => [
    //             'JsMinifier', 'GoogleAutoComplete', 'Klaviyo', 'CustomerSurvey', 'Maxmind', 'GoogleAutoAddressPopulate', 'CurrencyConverter', 
    //         ],
    //         'validation' => [
    //             'Xverify', 'Validator', 'NeverBounce', 'Mailgun', 'Lob', 
    //         ],
    //         'payment' => [
    //             'Paay_3DS', 'Paypal', 'AmazonPay', 'SepaPayment', 'Payu', 
    //         ],

    //     ];
    //     $extensions = json_decode(
    //         Http::get(
    //             $this->remoteListUrl
    //         ), true
    //     );
    //     $extensionList = [];
    //     foreach ($extensions as $key => $value) {
    //         $extensionList[$key] = $value;
    //         $extensionList[$key]['category'] = 'utility';
    //         $extensionList[$key]['author'] = 'CodeClouds';
    //         foreach ($categories as $catName => $catExLst) {
    //             if(in_array($value['extension_slug'], $catExLst)){
    //                 $extensionList[$key]['category'] = $catName;
    //             }
    //         }
    //     }
    //     return $extensionList;
    // }

    public function editConfigIds($id)
    {
        
        try
        {
            $extList = Request::form()->get('data');
            $extList = json_decode($extList);

            $this->saveActionCall();
            $data = json_decode(file_get_contents($this->dbFileName), 1);

            foreach($extList as $ext => $value) {
                $extSlug = $ext;

                $row  = array();
                if (!empty($data[$extSlug])) {
                    $row = $data[$extSlug];
                }
                
                if (!empty($row)) {
                    if($value) {
                        if(isset($row['configuration_ids']) && $row['configuration_ids'] !== '') {
                            $config_list = explode(',', $row['configuration_ids']);
                            if(!in_array($id, $config_list)){
                                array_push($config_list, $id);
                                $row['configuration_ids'] = (string) implode(',', $config_list); 
                            }
                        }
                        else {
                            $row['configuration_ids'] =  (string) implode(',', [$id]);
                        }
                    }
                    else {
                        if(isset($row['configuration_ids']) && $row['configuration_ids'] !== '') {
                            $config_list = explode(',', $row['configuration_ids']);
                            if(in_array($id, $config_list)){
                                unset($config_list[array_search($id, $config_list)]);
                                $row['configuration_ids'] = (string) implode(',', $config_list); 
                            }
                        }
                    }             
                }
                $data[$extSlug] = $row;
            }
            
            file_put_contents($this->dbFileName, json_encode($data), LOCK_EX);
            $activeData = $this->get($extSlug);
            $editData   = $activeData['data'][0];

            if (!$editData['edit_status'] == 0) {
                return array(
                    'success' => true,
                    'data'    => $editData,
                    'message' => 'All changes are saved successfully',
                );
            }

            return array(
                'success' => true,
                'data'    => $data,
            );


        } catch (Exception $ex) {
            return array(
                'success'       => false,
                'error_message' => $ex->getMessage(),
                'type'          => 'alert',
            );
        }
    }

    public function isExtensionActiveInConfig($id, $slug)
    {
        
        $this->extensionSlug = $slug;
        $temp = $this->getExtension($slug);
        $config_list = explode(',', $temp['datas']['configuration_ids']);
        if(in_array($id, $config_list)){
            return array(
                'success' => true,
            );
        }

        return array(
            'success' => false,
        );
    }

    public function securityCheck(){

        $validLicense = Security::isValidLicenseKey(Config::settings('domain'), Config::settings('license_key'), Config::settings('unify_authentication_key'));

        if (!$validLicense) {
            return array(
                'success'       => false,
                'error_message' => 'Unable to process. Your license key has expired.'
            );
        } 

        return array(
            'success' => true,
            'data'    => ''
        );
    }

    private function updateFramework() {
        if(Provider::checkExtensions('DeveloperTools')) {
            $upstream = new \Extension\DeveloperTools\Upstream();
            $output = $upstream->commitAndPull();

            return $output;
        }
        return true;
    }

    private function updateFrameworkVendor(){
        if(Provider::checkExtensions('ThirdPartyVendor')) {
            $download = new Extension\DeveloperTools\Download();
            $output = $download->downloadVendor();
            return $output;
        }
        return true;
    }
}
