<?php

namespace Admin\Controller;

use Application\Helper\Provider;
use Application\Helper\Security;
use Application\Request;
use Application\Session;
use Database\Connectors\ConnectionFactory;
use Exception;
use Lazer\Classes\Database;
use Lazer\Classes\Helpers\Validate;
use Lazer\Classes\LazerException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use PHPMailer\PHPMailer\PHPMailer;
use Application\Http;
use Application\Registry;
use Application\Config;

class SettingsController
{

    private $table;
    private static $dbConnection = null;

    public function __construct()
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
        $this->table = array(
            'name' => 'settings',
            'attr' => array(
                "id" => "integer",
                "license_key" => "string",
                "unify_authentication_key" => "string",
                "domain" => "string",
                "offer_path" => "string",
                "app_timezone" => "string",
                "development_mode" => "boolean",
                "allowed_country_codes" => "string",
                "enable_affiliates" => "boolean",
                "enable_rotators" => "boolean",
                "enable_browser_back_button" => "boolean",
                "db_engine" => "string", //new
                "db_host" => "string",
                "db_username" => "string",
                "db_password" => "string",
                "db_name" => "string",
                "db_port" => "string", //new
                "encryption_key" => "string",
                "data_expiration_time" => "string", //new
                "gateway_switcher_id" => "string",
                "force_https" => "boolean",
                "force_https_based_on_env" => "boolean",
                "force_www" => "boolean",
                "enable_browser_caching" => "boolean",
                "enable_gzip_compression" => "boolean",
                "mobile_path" => "string", //removed
                "mobile_url_path" => "string", //new
                "mobile_template_path" => "string", //new
                "enable_mobile_version" => "boolean",
                "mobile_version_only" => "boolean",
                "redirect_tablet_screen" => "boolean",
                "allow_direct_access" => "boolean",
                "enable_cdn" => "boolean",
                "enable_alternate_cdn_path" => "boolean",
                "cdn_basepath" => "string",
                "img_cdn_path" => "string",
                "css_cdn_path" => "string",
                "js_cdn_path" => "string",
                "allowed_card_types" => "string",
                "maximum_decline_attempts" => "string", //new
                "allowed_test_cards" => "string",
                "ga_site_id" => "string",
                "customer_support_email" => "string",
                "customer_service_number" => "string",
                "hours_of_operation" => "string",
                "return_address" => "string",
                "error_email_addresses" => "string",
                "corporate_address" => "string",
                "show_validation_errors" => "string",
                "country_lang_mapping" => "string",
                "db_verify" => "boolean",
                "disable_trialoffer_cardexp" => "boolean",
                "allow_global_session" => "boolean",
                "lk_verify" => "boolean",
                "smtp_verify" => "boolean",
                "from_email" => "string",
                "from_name" => "string",
                "smtp_name" => "string",
                "smtp_host" => "string",
                "smtp_port" => "string",
                "smtp_username" => "string",
                "smtp_password" => "string",
                "smtp_mode" => "string",
                "push_track_id" => "string"
            ),
        );

        try
        {
            Validate::table($this->table['name'])->exists();
        }
        catch (LazerException $ex)
        {
            Database::create(
                    $this->table['name'], $this->table['attr']
            );
        }
    }

    public function all()
    {
        try
        {
            $data = Database::table($this->table['name'])
                            ->findAll()->asArray();
            return array(
                'success' => true,
                'data' => $data,
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

    public function get($id = '')
    {
        try
        {
            $row = Database::table($this->table['name'])->where('id', '=', $id)->find()->asArray();
            $data = array();
            if (empty($row))
            {
                return array(
                    'success' => false,
                    'data' => array(),
                );
            }
            foreach ($this->table['attr'] as $key => $type)
            {
                $valueGet = $this->accessor->getValue($row[0], '[' . $key . ']');
                $data[$key] = ($valueGet !== null) ? $valueGet : '';
            }
            return array(
                'success' => true,
                'data' => $data,
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

    public function getDevMode($id)
    {
        try
        {
            $row = Database::table($this->table['name'])->where('id', '=', $id)->find();
            $data = array();
            if (empty($row))
            {
                return array(
                    'success' => false,
                    'data' => array(),
                );
            }
            $valueGet = isset($row->development_mode) ? $row->development_mode : '';
            $data['development_mode'] = !empty($valueGet) ? $valueGet : '';
            $data['timer_pending_seconds'] = (
                    filemtime(STORAGE_DIR . DS . '.development_mode') - time() + 3 * 60 * 60
                    );
            return array(
                'success' => true,
                'data' => $data,
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

    public function updateDevMode($id, $devMode)
    {
        try
        {
            $row = Database::table($this->table['name'])->where('id', '=', $id)->find()->asArray();
            $data = array();
            if (empty($row))
            {
                return array(
                    'success' => false,
                    'data' => array(),
                );
            }
            $rowUpdate = Database::table($this->table['name'])->find($id);
            foreach ($this->table['attr'] as $key => $type)
            {
                if ($key === 'id')
                {
                    continue;
                }
                $valueGet = $this->accessor->getValue($row[0], '[' . $key . ']');
                $value = ($valueGet !== null) ? $valueGet : '';
                $rowUpdate->{$key} = $this->filterValue($key, $value);
            }
            $rowUpdate->development_mode = (boolean) $devMode;

            touch(STORAGE_DIR . DS . '.development_mode');

            $rowUpdate->save();

            return array(
                'success' => true,
                'data' => array(
                    'development_mode' => $rowUpdate->development_mode,
                    'timer_pending_seconds' => 3 * 60 * 60,
                ),
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

    public function add()
    {
        try
        {
            $row = Database::table($this->table['name']);
            $data = array();
            foreach ($this->table['attr'] as $key => $type)
            {
                if ($key === 'id')
                {
                    continue;
                }
                $data[$key] = $row->{$key} = $this->filterInput($key);
            }
            if ($this->isValidData($row))
            {
                $row->save();
                return array(
                    'success' => true,
                    'data' => $data,
                );
            }
        }
        catch (Exception $ex)
        {
            return array(
                'success' => false,
                'data' => $data,
                'error_message' => $ex->getMessage(),
            );
        }
    }

    public function edit($id = '', $is_raw = false)
    {
        try
        {
            $trackingID = $this->getTrackingID();
            if(!empty($trackingID))
            {
                Request::form()->set('push_track_id', (string) $this->getTrackingID());
            }
            $row = Database::table($this->table['name'])->find($id);
            $data = array();
            foreach ($this->table['attr'] as $key => $type)
            {
                if ($key === 'id')
                {
                    continue;
                }
                if(!$is_raw)
                {
                    if (
                            $key === 'license_key' &&
                            $row->{$key} !== Request::form()->get('license_key')
                    )
                    {
                        $domain = Provider::removeSubDomain(trim(Request::getHttpHost(), '/'));
                        $isValidLisence = Security::isValidLicenseKey(
                                        $domain, Request::form()->get('license_key'), Request::form()->get('unify_authentication_key')
                        );
                        Session::set('isValidLicenseKey', $isValidLisence);
                    }
                    $data[$key] = $row->{$key} = $this->filterInput($key);
                }
            }
            if ($this->isValidData($row))
            {
                if(!empty($trackingID))
                {
                    $key = "push_track_id";
                    $row->{$key} =  Request::form()->get('push_track_id');
                }
                $row->save();
                return array(
                    'success' => true,
                    'data' => $data,
                );
            }
        }
        catch (Exception $ex)
        {
            return array(
                'success' => false,
                'data' => $data,
                'error_message' => $ex->getMessage(),
            );
        }
    }

    private function filterInput($key)
    {
        switch ($this->table['attr'][$key])
        {
            case 'integer':
                return Request::form()->getInt($key, 0);
            case 'boolean':
                return (boolean) Request::form()->get($key, false);
            default:
                return Request::form()->get($key, '');
        }
    }

    private function filterValue($key, $value)
    {
        switch ($this->table['attr'][$key])
        {
            case 'integer':
                return (integer) $value;
            case 'boolean':
                return (boolean) $value;
            default:
                return $value;
        }
    }

    private function isValidData($data)
    {
        return true;
    }

    public function timezone_identifiers_list()
    {
        return array(
            'success' => true,
            'data' => json_encode(timezone_identifiers_list()),
        );
    }

    public function checkDatabaseConnection()
    {
        try
        {
            $dbHost = $this->filterInput('db_host');
            $dbUsername = $this->filterInput('db_username');
            $dbPassword = $this->filterInput('db_password');
            $dbName = $this->filterInput('db_name');

            if(empty($dbHost) || empty($dbUsername) || empty($dbName)) {
                return array(
                    'success' => true,
                    'data' => array('db_verify' => false),
                );
            }

            $connection = $this->getDatabaseConnection($dbHost, $dbUsername, $dbPassword, $dbName);
            $this->updateDbVerify(is_object($connection));
            return array(
                'success' => true,
                'data' => array('db_verify' => is_object($connection)),
            );
        }
        catch (Exception $ex)
        {
            return array(
                'success' => false,
                'data' => array('db_verify' => false),
                'error_message' => $ex->getMessage(),
            );
        }
    }

    private function getDatabaseConnection($dbHost, $dbUsername, $dbPassword, $dbName)
    {
        try
        {
            $factory = new ConnectionFactory();
            self::$dbConnection = $factory->make(array(
                'driver' => 'mysql',
                'host' => $dbHost,
                'username' => $dbUsername,
                'password' => $dbPassword,
                'database' => $dbName,
                'charset' => 'utf8',
                'collation' => 'utf8_unicode_ci',
            ));
        }
        catch (Exception $ex)
        {
            return false;
        }
        return self::$dbConnection;
    }

    public function updateDbVerify($status)
    {
        try
        {
            $row = Database::table($this->table['name'])->where('id', '=', 1)->find()->asArray();
            $data = array();
            $rowUpdate = Database::table($this->table['name'])->find(1);
            foreach ($this->table['attr'] as $key => $type)
            {
                if ($key === 'id')
                {
                    continue;
                }
                $valueGet = $this->accessor->getValue($row[0], '[' . $key . ']');
                $value = ($valueGet !== null) ? $valueGet : '';
                $rowUpdate->{$key} = $this->filterValue($key, $value);
            }
            $rowUpdate->db_verify = (boolean) $status;
            $rowUpdate->save();
        }
        catch (Exception $ex)
        {
            
        }
    }

    public function removeWrongInstance($param)
    {
        if (empty($param))
        {
            return;
        }
        foreach ($param as $key => $value)
        {
            if (is_array($value))
            {
                $value = json_encode($value);
            }
            settype($value, $this->table['attr'][$key]);
            Request::form()->set($key, $value);
        }
        return $this->edit($param['id']);
    }

    public function validateLicense()
    {
        $domain = Provider::removeSubDomain(trim(Request::getHttpHost(), '/'));
        return Security::isValidLicenseKey(
                        $domain, Request::form()->get('license_key'), Request::form()->get('unify_authentication_key')
        );
    }

    public function checkSmtp()
    {
        $config = Request::form()->all();

        $mail = new PHPMailer(false);
        try
        {
            //Server settings
            $mail->SMTPDebug = 0;                                       // Enable verbose debug output
            $mail->isSMTP();                                            // Set mailer to use SMTP
            $mail->Host = trim($config['smtp_host']);  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                                   // Enable SMTP authentication
            $mail->Username = trim($config['smtp_username']);                     // SMTP username
            $mail->Password = trim($config['smtp_password']);                               // SMTP password
            $mail->SMTPSecure = trim($config['smtp_mode']);                                // Enable TLS encryption, `ssl` also accepted
            $mail->Port = trim($config['smtp_port']);
            ;                                    // TCP port to connect to


            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => false,
                )
            );


            $mail->setFrom(trim($config['from_email']));
            $mail->addAddress(trim($config['from_email']));

            $mail->Subject = 'SMTP Connection Test.';
            $mail->Body = 'SMTP Connection test in Unify Dashboard.';


            if ($mail->send())
            {
                return array(
                    'success' => true,
                    'data' => null,
                    'success_message' => 'SMTP has been verified successfully.',
                );
            }
            else
            {
                return array(
                    'success' => false,
                    'data' => null,
                    'error_message' => 'SMTP verification failed.',
                );
            }
        }
        catch (Exception $e)
        {

            return array(
                'success' => false,
                'data' => null,
                'error_message' => $mail->ErrorInfo === '' ? 'SMTP verification failed.' : $mail->ErrorInfo,
            );
        }

        return $config;
    }
    
    public function getTrackingID()
    {
        $offerUrl = sprintf('%s/', rtrim(Request::getOfferUrl(), '/'));
        $gateWaySwitcherId = Config::settings('gateway_switcher_id');
        $remoteSettings = Config::extensionsConfig('TrafficLoadBalancer.default_settings.enable_remote');
        if (empty($remoteSettings) || empty($gateWaySwitcherId))
        {
            return false;
        }
        $queryParams = array(
            'offer_url' => $offerUrl,
            'conf_scrap_count' => 0,
        );
        $queryString = http_build_query($queryParams);
        $apiEndpoint = rtrim(Registry::system('systemConstants.201CLICKS_URL'), '/api');
        $url = sprintf(
                '%s/scrapper/%s/?%s', $apiEndpoint, $gateWaySwitcherId, $queryString
        );
        $response = json_decode(Http::get($url), true);
        $data = json_decode($response['data'], true);
        return $data['unique_id'];
    }

}
