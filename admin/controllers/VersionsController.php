<?php

namespace Admin\Controller;

use Application\Request;
use Exception;

class VersionsController
{
    private $table, $extensionList, $extensionDir, $multiSelects;

    public function __construct()
    {
    }

    public function getVersions($db)
    {
        $subFiles        = array();
        $subFilesWthTime = array();
        try
        {
            foreach (glob(LAZER_BACKUP_PATH . DS . $db . '-*.data.json') as $file) {
                $time                      = str_replace($db . '-', "", basename($file, ".data.json"));
                $subFiles[basename($file)] = $time;
            }
            asort($subFiles, SORT_NUMERIC);
            foreach ($subFiles as $key => $val) {
                $subFilesWthTime[$key] = date('l d F Y h:i A', strtotime($val));
            }
            return $subFilesWthTime;
        } catch (Exception $ex) {
            return array(
                'success'       => false,
                'data'          => array(),
                'error_message' => $ex->getMessage(),
            );
        }
    }

    public function restoreVersion()
    {
        $formData = Request::form()->all();
        $mainData = json_decode(
            file_get_contents(
                sprintf('%s%s%s.data.json', LAZER_DATA_PATH, DS, $formData['name'])
            ), 1
        );

        if (empty($formData) || !array_key_exists('db', $formData) || !array_key_exists('dataId', $formData)) {
            return array(
                'success'       => false,
                'error_message' => 'Select version',
            );
        }

        $database = json_decode(
            file_get_contents(LAZER_BACKUP_PATH . DS . $formData['db']), 1);

        foreach ($database as $key => $value) {
            if ($value['id'] == $formData['dataId']) {
                $data = $value;
            }
        }

        if (empty($mainData) || !is_array($mainData)) {
            $mainData = array($data);
        } else {
            foreach ($mainData as $dataKey => $dataValue) {
                if ($dataValue['id'] == $data['id']) {
                    $mainData[$dataKey] = $data;
                    break;
                }
            }
        }

        file_put_contents(
            sprintf('%s%s%s.data.json', LAZER_DATA_PATH, DS, $formData['name']),
            json_encode($mainData),
            LOCK_EX
        );
        return array(
            'success' => true,
            'message' => 'Data replaced',
        );
    }
}
