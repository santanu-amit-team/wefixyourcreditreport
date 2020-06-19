<?php

namespace Admin\Controller;

use Admin\Library\ResetSettings;
use Application\Config;
use Application\Helper\Alert;
use Application\Helper\Provider;
use Application\Helper\Security;
use Application\Http;
use Application\Registry;
use Application\Request;
use Exception;
use Lazer\Classes\Database;
use Lazer\Classes\Helpers\Validate;
use Lazer\Classes\LazerException;
use Symfony\Component\PropertyAccess\PropertyAccess;

class AdvancedController
{

    private $table;
    private $extensionExistsMsg;

    public function __construct()
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
        $this->table    = array(
            'name' => 'advanced',
            'attr' => array(
                "id"       => "integer",
                "scrapper" => "string",
            ),
        );
        $this->extensionExistsMsg = array(
            'not_installed' => 'Extension is not installed',
            'not_active'    => 'Extension is not active',
            'no_table'      => 'Table doesnot exist',
        );

        try
        {
            Validate::table($this->table['name'])->exists();
        } catch (LazerException $ex) {
            Database::create(
                $this->table['name'], $this->table['attr']
            );
        }
        $this->checkDataFile();
    }

    public function checkDataFile()
    {
        $fileUrl = LAZER_DATA_PATH . $this->table['name'] . ".data.json";
        if (file_exists($fileUrl)) {
            $advancedData = file_get_contents($fileUrl);
            $advancedData = json_decode($advancedData, true);

            if (empty($advancedData)) {
                $this->add();
            }
        }
        return array(
            'success' => true,
            'data'    => array(),
        );
    }

    public function all()
    {
        try
        {
            $data = Database::table($this->table['name'])
                ->findAll()->asArray();
            return array(
                'success' => true,
                'data'    => $data,
            );
        } catch (Exception $ex) {
            return array(
                'success'       => false,
                'data'          => array(),
                'error_message' => $ex->getMessage(),
            );
        }
    }

    public function get($id = '')
    {
        try
        {
            $data                     = array();
            $data['advancedPassword'] = file_get_contents(LAZER_DATA_PATH . '.passwords' . DS . '.master');
            $row                      = Database::table($this->table['name'])->where('id', '=', $id)->find()->asArray();
            if (empty($row)) {
                return array(
                    'success' => true,
                    'data'    => $data,
                );
            }
            foreach ($this->table['attr'] as $key => $type) {
                $valueGet   = $this->accessor->getValue($row[0], '[' . $key . ']');
                $data[$key] = ($valueGet !== null) ? $valueGet : '';
            }

            return array(
                'success' => true,
                'data'    => $data,
            );
        } catch (Exception $ex) {
            return array(
                'success'       => false,
                'data'          => array(),
                'error_message' => $ex->getMessage(),
            );
        }
    }

    public function add()
    {
        try
        {
            $row  = Database::table($this->table['name']);
            $data = array();
            foreach ($this->table['attr'] as $key => $type) {
                if ($key === 'id') {
                    continue;
                }
                $data[$key] = $row->{$key} = $this->filterInput($key);
            }
            if ($this->isValidData($row)) {
                $row->save();
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

    public function edit($id = '' , $is_raw = false)
    {
        try
        {
            $row  = Database::table($this->table['name'])->find($id);
            $data = array();
            foreach ($this->table['attr'] as $key => $type) {
                if ($key === 'id') {
                    continue;
                }
                if(!$is_raw)
                {
                    $data[$key] = $row->{$key} = $this->filterInput($key);
                }
                
            }
            if ($this->isValidData($row)) {
                $rowRec                = json_decode($row->scrapper);
                $rowRec->push_track_id = $this->getTrackingID();
                $row->scrapper         = json_encode($rowRec);

                $row->save();
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

    private function isValidData($data)
    {
        return true;
    }

    public function extensionExists()
    {
        $data = [];
        $msg  = '';
        try
        {
            $data = Database::table('extensions')
                ->where('extension_slug', '=', 'TrafficLoadBalancer')
                ->findAll()->asArray();
            $status = !empty($data) ? (($data[0]['active']) ? true : false) : false;
            $msg    = !empty($data) ? (($data[0]['active']) ? '' : $this->extensionExistsMsg['not_active']) : $this->extensionExistsMsg['not_installed'];

            if (class_exists('\Extension\TrafficLoadBalancer\Setup') && $status) {
                $storageFolderCheck = \Extension\TrafficLoadBalancer\Setup::cacheFolderChecking();
                if (!$storageFolderCheck['success']) {
                    $status = $storageFolderCheck['success'];
                    $msg    = $storageFolderCheck['error_message'];
                    $data   = array(
                        'identifier'    => 'Cache directory',
                        'text'          => $storageFolderCheck['error_message'],
                        'type'          => 'error',
                        'alert_handler' => 'extensions',
                    );
                    Alert::insertData($data);
                } else {
                    $tableCheck = \Extension\TrafficLoadBalancer\Setup::tableExists();
                    $status     = $tableCheck['success'];
                    $msg        = !$status ? $this->extensionExistsMsg['no_table'] : '';
                }
            }

            return array(
                'success'       => $status,
                'data'          => $data,
                'error_message' => $msg,
            );
        } catch (Exception $ex) {
            return array(
                'success'       => false,
                'data'          => $data,
                'error_message' => $ex->getMessage(),
            );
        }
    }

    public function checkGatewaySwitcher()
    {
        $data = [];
        try
        {
            $msg          = 'Almost20 Instance ID is not set';
            $status       = false;
            $settingsData = Database::table('settings')->find('1');
            if (isset($settingsData->gateway_switcher_id)) {
                $status = !empty($settingsData->gateway_switcher_id) ? true : false;
                if ($status) {
                    $msg = '';
                }
            }
            return array(
                'success'       => $status,
                'data'          => $data,
                'error_message' => $msg,
            );
        } catch (Exception $ex) {
            return array(
                'success'       => false,
                'data'          => $data,
                'error_message' => $ex->getMessage(),
            );
        }
    }

    public function checkDomainSwitch()
    {
        $domain = Provider::removeSubDomain(
            trim(Request::getHttpHost(), '/')
        );
        return array(
            'success'  => true,
            'switched' => Security::isDomainChanged($domain),
        );
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

    public function getTrackingID()
    {
        $offerUrl          = sprintf('%s/', rtrim(Request::getOfferUrl(), '/'));
        $gateWaySwitcherId = Config::settings('gateway_switcher_id');
        $remoteSettings = Config::advanced('scrapper.remote');        
        if(empty($remoteSettings))
        {
            return array(
                'success'       => false,
            );
        }
        $queryParams       = array(
            'offer_url'        => $offerUrl,
            'conf_scrap_count' => 0,
        );
        $queryString = http_build_query($queryParams);
        $apiEndpoint = rtrim(Registry::system('systemConstants.201CLICKS_URL'), '/api');
        $url         = sprintf(
            '%s/scrapper/%s/?%s', $apiEndpoint, $gateWaySwitcherId, $queryString
        );
        $response = json_decode(Http::get($url), true);
        $data     = json_decode($response['data'], true);
        return $data['unique_id'];
    }

}
