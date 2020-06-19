<?php

namespace Application\Helper;

use Application\Http;
use Application\Registry;
use Application\Config;

class Security
{
    private static $unifyErrorsStatusCode = [
        401, 400
    ];

    private function __construct()
    {
        return;
    }

    public static function isValidLicenseKey($domain, $licenseKey, $authKey = '')
    {
        if(empty( $licenseKey) || empty( $domain)) {
            return false;
        }
        
        $url = sprintf(
            '%s/validate-codebase-license/', Registry::system('systemConstants.UNIFY_LIVE_URL')
        );

        if(empty($authKey)) {
            $authKey = Config::settings('unify_authentication_key');
        }

        $response = Http::post(
            $url, array(
                'license_key' => $licenseKey,
                'domain_name' => $domain,
            ),
            array(
                'X-Unify-Auth' => sprintf('%s', $authKey)
            )
        );
        $response = !empty(self::isJSON($response)) ? json_decode($response, true) : $response;

        if ( (
                !empty($response['curlError']) && 
                array_key_exists('httpCode', $response) &&
                !in_array((int) $response['httpCode'], self::$unifyErrorsStatusCode)
             ) ||
             !empty($response['success'])
            ) {
            return true;
        }
        return false;
    }
    
    public static function isDomainChanged($currentDomain)
    {
        $registryFile = STORAGE_DIR . DS . '.domain_registry';
        if (!file_exists($registryFile)) {
            return true;
        }
        $registeredDomain = file_get_contents($registryFile);
        if ($registeredDomain === $currentDomain) {
            return false;
        }
        return true;
    }

    public static function registerDomain($currentDomain)
    {
        $registryFile     = STORAGE_DIR . DS . '.domain_registry';
        $registeredDomain = file_put_contents($registryFile, $currentDomain, LOCK_EX);
    }

    public static function encrypt($content, $secureKey)
    {
        $plainText = serialize($content);
        $iv        = mcrypt_create_iv(
            mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC), MCRYPT_DEV_URANDOM
        );

        $key = substr(pack('H*', $secureKey), 0, 32);

        $mac         = hash_hmac('sha256', $plainText, substr(bin2hex($key), -32));
        $chipherText = mcrypt_encrypt(
            MCRYPT_RIJNDAEL_256, $key, $plainText . $mac, MCRYPT_MODE_CBC, $iv
        );

        return base64_encode($chipherText) . '|' . base64_encode($iv);
    }

    public static function decrypt($content = null, $secureKey = null)
    {
        $contentParts = explode('|', $content . '|');
        $chipherText  = base64_decode($contentParts[0]);
        $iv           = base64_decode($contentParts[1]);

        if (strlen($iv) !== mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC)) {
            return false;
        }

        $key = substr(pack('H*', $secureKey), 0, 32);

        $decoded = trim(
            mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $chipherText, MCRYPT_MODE_CBC, $iv)
        );
        $mac           = substr($decoded, -64);
        $plainText     = substr($decoded, 0, -64);
        $calculatedMac = hash_hmac('sha256', $plainText, substr(bin2hex($key), -32));

        if ($calculatedMac !== $mac) {
            return false;
        }

        return unserialize($plainText);
    }

    private static function isJSON($string) {
        return is_string($string) &&
            is_array(json_decode($string, true)) &&
            (json_last_error() == JSON_ERROR_NONE) ? true : false;
    }
}
