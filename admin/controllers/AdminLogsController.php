<?php

namespace Admin\Controller;

use Exception;
use Lazer\Classes\Database;
use Lazer\Classes\Helpers\Validate;
use Lazer\Classes\LazerException;
use Application\Request;
use Application\Config;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Database\Connectors\ConnectionFactory;
use DateTime;

class AdminLogsController
{

    private $table, $database;
    private static $dbConnection = null;

    public function __construct()
    {
        $this->table              = 'adminlogs';
        $this->database           = STORAGE_DIR . DS . 'adminlogs.sqlite';
    }

    private function getDatabaseConnection()
    {
        try
        {
            if (!extension_loaded('pdo_sqlite')) {
                throw new Exception('PDO extension does not exists');
            }
            if (!file_exists($this->database)) {
                file_put_contents($this->database, '');
            }
            if (!is_writable($this->database)) {
                throw new Exception('Check write permission of database file');
            }
            $factory    = new ConnectionFactory();
            $connection = $factory->make(array(
                'driver'   => 'sqlite',
                'database' => $this->database,
            ));
            return $connection;
        } catch (Exception $ex) {
             throw($ex);
        }
    }

    public function all($campaignType = '')
    {
        try {
            $row   = array();
            $query =  $this->getDatabaseConnection()->table($this->table)
                ->select('id', 'event', 'logs', 'ipAddress', 'created_on')
                ->orderBy('id', 'desc');

            $totalRows = $this->getDatabaseConnection()->table($this->table)
                ->count();
            if (Request::form()->has('offset', 'limit') && Request::form()->get('limit') != 'all') {
                $data = $query
                    ->offset(Request::form()->get('offset'))
                    ->limit(Request::form()->get('limit'))
                    ->get();
                $data = $this->modifyData($data);

                return array(
                    'success'   => true,
                    'data'      => $data,
                    'totalData' => $totalRows,
                );
            }

            $data = $query
                ->get();
            
            $data = $this->modifyData($data);
            //gmdate("Y-m-d\TH:i:s\Z")
            return array(
                'success'   => true,
                'data'      => $data,
                'totalData' => $totalRows,
            );
        } catch (Exception $ex) {
            return array(
                'success'       => false,
                'error_message' => $ex->getMessage(),
            );
        }
    }
    
    public function modifyData($data)
    {
        foreach ($data as $listKey => $listValue) {
            if(!empty($data[$listKey]['created_on'])){
                $source = $data[$listKey]['created_on'];
                $date = new DateTime($source);
                $data[$listKey]['created_on'] = $date->format('Y-m-d\TH:i:s\Z');
            }
        }
        return $data;
    }

    public function checkExtensions()
    {
        $result = array(
            'success' => true,
            'extensionAdminLogsActive' => false,
        );
        $extensions = Config::extensions();

        $extensions = Config::extensions();
        foreach ($extensions as $extension)
        {
            if ($extension['extension_slug'] !== 'AdminLogs')
            {
                continue;
            }
            if ($extension['active'] === true)
            {
                $result['extensionAdminLogsActive'] = true;
            }
            break;
        }

        return $result;
    }

}
