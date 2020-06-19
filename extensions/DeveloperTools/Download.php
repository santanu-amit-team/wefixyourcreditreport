<?php

namespace Extension\DeveloperTools;

use Application\Http;
use Application\Registry;
use Application\Request;
use Application\Config;
use \Exception;
use PclZip;

class Download
{

    const ERR_NO_UNMATCHED = 'Branch is in sync with master';
    const ERR_UPSTREAM_EX = 'Upstream error';
    const ERR_UPSTEAM_HASH = 'Upstream file hash error';
    const ERR_UPSTEAM_SYNC = 'No updates are available in Upstream';
    const ERR_PERM = 'Permission error';
    const DOWNSTREAM_BK = 'downstream';
    const COMMIT = '.commit';
    const SYNCED = 'Great! Framework has been upgraded to a newer version';

    public function __construct()
    {
        $this->updateSource = Http::get(
                        Registry::system('systemConstants.REMOTE_URL') . 'updates.php'
        );

        if (empty($this->updateSource))
        {

            throw new Exception(self::ERR_UPSTEAM_SYNC);
        }

        $this->updateSource = json_decode($this->updateSource);
    }

    public function downloadVendor()
    {
        set_time_limit(0);

        $downloadUrl = Registry::system('systemConstants.REMOTE_URL') . 'download_vendor.php?auth_key=u3ERtye9BDkaFk6R';

        $path = 'vendor.zip';

        $fp = fopen($path, 'w');
        try
        {
            $response = Http::download($downloadUrl, $fp);
            if (!empty($response['httpCode']) && $response['httpCode'] == 401)
            {
                throw new Exception(
                'Coudn’t verify license key, '
                . 'please enter a valid license key in settings to download this extension.'
                );
            }
            else if (!empty($response['httpCode']) && $response['httpCode'] == 503)
            {
                throw new Exception('Service Unavailable.');
            }
            else if ($response !== true && !empty($response['httpCode']) && $response['httpCode'] == 404)
            {
                throw new Exception('Sorry, couldn’t locate the extension.');
            }
            else if ($response !== true)
            {
                throw new Exception($response['error']);
            }

            $archive = new PclZip($path);
            if (!$archive->extract(PCLZIP_OPT_PATH, BASE_DIR, PCLZIP_OPT_REPLACE_NEWER))
            {
                throw new Exception('Unzip proccess failed');
            }
            fclose($fp);
            unlink($path);

            rename('vendor', 'vendor_bck');
            rename('master', 'vendor');
            exec('rm -rf vendor_bck');

            return array(
                'success' => true,
                'message' => 'Vendor has been updated successfully.',
            );
        }
        catch (Exception $ex)
        {
            fclose($fp);
            unlink($path);
            return array(
                'success' => false,
                'error_message' => $ex->getMessage(),
            );
        }
    }

}
