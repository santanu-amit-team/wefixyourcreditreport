<?php

/*
 * To Reset Database Files
 * To Reset Registry Json
 * To Reset Extensions Data.
 * To Reset Master Password
 * To Reset Sqlite database
 */

namespace Admin\Library;

use Exception;
use Lazer\Classes\Database;

class ResetSettings
{
    private static $storageFilesToNotReset = array(
        'advanced', 'settings', 'extensions', 'extensionsConfig',
    );
    private static $settingsDefaultDataKeys = array(
        'id', 'offer_path', 'app_prefix', 'app_timezone', 'allowed_country_codes',
        'allowed_card_types', 'country_lang_mapping', 'show_validation_errors'
    );
    private static $advancedDefaultDataKeys = array(
        'id',
    );
    private static $extensionsConfigDataJson = null;
    

    public static function getStorageFiles($fileExtension)
    {
        $storageFiles = glob(LAZER_DATA_PATH . '*' . $fileExtension);
        $fileList     = array();

        if (is_array($storageFiles) && !empty($storageFiles)) {
            //fileList arrange
            foreach ($storageFiles as $storageFile) {

                $baseName   = basename($storageFile);
                $fileType   = strstr($baseName, '.');
                $fileName   = strstr($baseName, '.', true);
                $fileList[] = array(
                    'fileType' => $fileType,
                    'fileName' => $fileName,
                    'filePath' => $storageFile,
                );
            }
        }

        return $fileList;
    }

    public static function resetTables($fileType)
    {
        $fileList = self::getStorageFiles($fileExtension = $fileType.'.json');
        foreach ($fileList as $key => $file) {
            $flag = 0;
            foreach (self::$storageFilesToNotReset as $value) {
                if ($file['fileName'] === $value) {
                    $flag = 1;
                    break;
                }
            }
            if ($flag == 0) {
                if($fileType == 'data'){
                   Database::table($file['fileName'])->delete(); 
                }
                elseif($fileType == 'config'){
                    $configArray = json_decode(file_get_contents($file['filePath']), true);
                    $configArray['last_id'] = 0;
                    file_put_contents($file['filePath'], json_encode($configArray), LOCK_EX);
                }
            }
        }
    }

    public static function resetSettingsData($fileName, $ignoreKeys)
    {
        $settingsDefaultDataKeys = $ignoreKeys;
        $settingsDataToSave      = array();
        $settingsData            = json_decode(
            file_get_contents(LAZER_DATA_PATH . $fileName.'.data.json'), true
        );
        $settingsConfig          = json_decode(
            file_get_contents(LAZER_DATA_PATH . $fileName.'.config.json'), true
        );
        if (is_array($settingsData) && !empty($settingsData)) {
            $settingsData = $settingsData[0];
        }
        foreach ($settingsData as $dataKey => $dataValue) {
            $flag         = 0;
            $defaultValue = $dataValue;
            foreach ($settingsDefaultDataKeys as $key) {
                if ($dataKey == $key) {
                    $flag = 1;
                    break;
                }
            }
            if ($flag === 0 && array_key_exists($dataKey,$settingsConfig['schema'])) {
                if ($settingsConfig['schema'][$dataKey] == 'integer') {
                    $defaultValue = 0;
                } elseif ($settingsConfig['schema'][$dataKey] == 'boolean') {
                    $defaultValue = false;
                } else {
                    $defaultValue = "";
                }
            }
            if(array_key_exists($dataKey,$settingsConfig['schema'])){
                $settingsDataToSave[0][$dataKey] = $defaultValue;
            }
        }
        if (!$settingsConfig['last_id'] == 1) {
            $settingsConfig['last_id'] = 1;
            file_put_contents(
                LAZER_DATA_PATH . $fileName.'.config.json', json_encode($settingsConfig), LOCK_EX
            );
        }
        if (!empty($settingsDataToSave)) {
            file_put_contents(
                LAZER_DATA_PATH . $fileName.'.data.json', json_encode($settingsDataToSave), LOCK_EX
            );
        }
    }

    public static function resetExtensionsData()
    {
        $extensionDataToSave = array();
        self::$extensionsConfigDataJson = LAZER_DATA_PATH . 'extensionsConfig.data.json';
        if(file_exists(self::$extensionsConfigDataJson)){
            unlink(self::$extensionsConfigDataJson);    
        }
        
        $extensionsData = json_decode(
            file_get_contents(LAZER_DATA_PATH . 'extensions.data.json'),
            true);

        if (is_array($extensionsData) && !empty($extensionsData)) {
            foreach ($extensionsData as $key => $value) {
                $value['active'] = false;
                if ($value['edit_status'] == 1) {
                    $value['edit_status'] = 0;
                }
                $extensionDataToSave[] = $value;
            }
        }
        if (!empty($extensionDataToSave)) {
            file_put_contents(
                LAZER_DATA_PATH . 'extensions.data.json',
                json_encode($extensionDataToSave),
                LOCK_EX
            );
        }
        file_put_contents(STORAGE_DIR . DS . 'registry.json', json_encode(array()), LOCK_EX);
    }

    public static function randomPassword($length, $characters)
    {
        $symbols      = array();
        $used_symbols = '';
        $pass         = '';

        $symbols["lower_case"]      = 'abcdefghijklmnopqrstuvwxyz';
        $symbols["upper_case"]      = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $symbols["numbers"]         = '1234567890';
        $symbols["special_symbols"] = '!?~@#-_+<>[]{}';

        $characters = explode(",", $characters);       // get characters types to be used for the passsword
        foreach ($characters as $key => $value) {
            $used_symbols .= $symbols[$value];        // build a string with all characters
        }
        $symbols_length = strlen($used_symbols) - 1; //strlen starts from 0 so to get number of characters deduct 1
            for ($i = 0; $i < $length; $i++) {
                $n = rand(0, $symbols_length);      // get a random character from the string with all characters
                $pass .= $used_symbols[$n];        // add the character to the password string
            }       

        return $pass;                            // return the generated password
    }

    public static function deleteSqliteDb(){
        $fileList = glob(STORAGE_DIR . DS . '*.sqlite'); 
        if(is_array($fileList) && !empty($fileList)){
            foreach ($fileList as $filePath) {
                unlink($filePath);
            }
        }
    }

    protected static function setPassword($path)
    {
        if(file_exists($path)){
            if (!is_writable($path)) {
                throw new Exception('Write error: ' . $path);
            }
        }

        $password = self::randomPassword(10, "lower_case,upper_case,numbers,special_symbols");

        file_put_contents($path, $password, LOCK_EX);
    }

    protected static function removeBackups()
    {
        if(!file_exists(LAZER_BACKUP_PATH)){
            return;
        }
        array_map( 'unlink', glob(LAZER_BACKUP_PATH . '/*.json') );
        rmdir(LAZER_BACKUP_PATH);
    }

    public static function resetStorage()
    {
        $fileList               = array();
        $configFileList         = self::getStorageFiles($fileExtension = 'config.json');

        //remove json data
        self::resetTables($fileType = 'data');        

        // reset all configuration last_id

        self::resetTables($fileType = 'config');

        //save default data for settings

        self::resetSettingsData('settings', self::$settingsDefaultDataKeys);

        //save default data for advanced

        self::resetSettingsData('advanced', self::$advancedDefaultDataKeys);

        //change extension data

        self::resetExtensionsData();

        //Update passwords
        self::setPassword(LAZER_DATA_PATH . DS . '.passwords' . DS . '.master');
        self::setPassword(LAZER_DATA_PATH . DS . '.passwords' .DS . '.offeradmin');
        self::setPassword(LAZER_DATA_PATH . DS . '.passwords' .DS . '.webmaster');

        //delete sqLite db

        self::deleteSqliteDb();

        //Remove backups
        self::removeBackups();

        return;
    }
}
