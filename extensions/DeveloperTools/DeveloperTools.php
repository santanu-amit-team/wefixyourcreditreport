<?php
namespace Extension\DeveloperTools;

use Application\Http;
use Application\Registry;
use Lazer\Classes\Database;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Admin\Controller\ExtensionsController;
use Application\Request;
use Exception;

class DeveloperTools
{

    const slug = 'Developer Tools';

    public function __construct()
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    public function runNightlyPatches()
    {

        $remoteExtensions = json_decode(
            Http::get(
                Registry::system('systemConstants.REMOTE_LIST_URL')
            ), true
        );

        foreach ( $remoteExtensions as $extensions ) {

            $localExtension = Database::table('extensions')
                                ->where('extension_slug', '=', $extensions['extension_slug'])
                                    ->find()
                                        ->asArray();

            $localVersion  = $this->accessor->getValue($localExtension, '[0][version]');
            $remoteVersion = $this->accessor->getValue($extensions, '[version]');

            $localSemanticVersion = explode('.', $localVersion);
            $remoteSemanticVersion = explode('.', $remoteVersion);

            $diffInSemantic = array_diff_assoc($remoteSemanticVersion, $localSemanticVersion);

            print_r($diffInSemantic);

            if ( count($diffInSemantic) == 1 && ! empty($diffInSemantic[2]) ) {

                echo 'Patch needed: ' . $this->accessor->getValue($localExtension, '[0][extension_name]') . "\n";

                \Application\Helper\Alert::insertData(array(
                    'identifier' => $this->accessor->getValue($localExtension, '[0][extension_name]'),
                    'text' => sprintf('New update available! This includes some exciting new features and improvements in the extension.'),
                    'type' => 'error',
                ));

            }

        }
    }
    
    public function activate()
    {
        try{
            $fileUrl = STORAGE_DIR.DS.'.htaccess';
            if(!file_exists($fileUrl))
            {
                $fileName     = STORAGE_DIR . DS . '.htaccess';
                touch($fileName);
                file_put_contents($fileName, "Order Allow,Deny\nDeny from all");
                chmod($fileName, 0777); 
            }
            
        } catch (Exception $ex) {

        }
    }

}