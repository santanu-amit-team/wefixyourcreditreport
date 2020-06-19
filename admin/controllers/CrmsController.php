<?php

namespace Admin\Controller;

use Exception;
use Lazer\Classes\Database;
use Lazer\Classes\Helpers\Validate;
use Lazer\Classes\LazerException;
use Application\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Application\Model\Limelight;
use Application\Model\Konnektive;
use Application\Model\Responsecrm;
use Application\Config;
use Database\Connectors\ConnectionFactory;

class CrmsController
{

    private $request, $table;
    private static $dbConnection = null;

    public function __construct()
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();

        $this->crmTypes = array('limelight', 'konnektive', 'responsecrm', 'velox', 'emanage', 'infusionsoft', 'sixcrm', 'nmi','m1billing', 'limelightv2', 'layer2', 'vrio');

        $this->table = array(
            'name' => 'crms',
            'attr' => array(
                'id' => 'integer',
                'crm_label' => 'string',
                'crm_type' => 'string',
                'endpoint' => 'string',
                'username' => 'string',
                'password' => 'string',
                'account' => 'string',
                'verified' => 'boolean',
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
            $rows = Database::table($this->table['name'])->orderBy('id', 'desc')->findAll()->asArray();
            $result = array();
            foreach ($rows as $row)
            {
                $data = array();
                foreach ($this->table['attr'] as $key => $type)
                {
                    $valueGet = $this->accessor->getValue($row, '[' . $key . ']');
                    $data[$key] = ($valueGet !== NULL) ? $valueGet : '';
                }
                array_push($result, $data);
            }
            return array(
                'success' => true,
                'data' => $result,
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
                $data[$key] = ($valueGet !== NULL) ? $valueGet : '';
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
                $data[$key] = $row->{$key} = ($key == 'verified') ? true : $this->filterInput($key);
            }
            if ($this->isValidData($row))
            {
                $row->save();
                $data['id'] = $row->id;
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

    public function edit($id = '')
    {
        try
        {
            $row = Database::table($this->table['name'])->find($id);
            $data = array();
            foreach ($this->table['attr'] as $key => $type)
            {
                if ($key === 'id')
                {
                    continue;
                }
                $data[$key] = $row->{$key} = ($key == 'verified') ? true : $this->filterInput($key);
            }
            if ($this->isValidData($row))
            {
                $row->save();
                $data['id'] = $row->id;
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

    // public function delete($id)
    // {
    //     try
    //     {
    //         $data_count = Database::table('configurations')->where('crm_id', '=', $id)->find()->count();

    //         if ($data_count)
    //         {
    //             return array(
    //                 'success' => false,
    //                 'data' => array(),
    //                 'error_message' => 'Sorry! This CRM is already used in Configuration',
    //             );
    //         }

    //         Database::table($this->table['name'])->find($id)->delete();
    //         return array(
    //             'success' => true,
    //             'data' => array()
    //         );
    //     }
    //     catch (Exception $ex)
    //     {
    //         return array(
    //             'success' => false,
    //             'data' => array(),
    //             'error_message' => $ex->getMessage(),
    //         );
    //     }
    // }

    public function delete($id='')
    {
        try
        {
            $selectedIds = ($id == '') ? Request::get('ids') : array($id);
            $deletedIds = $notDeletedIds = [];
            $data_count = 0;
            foreach ($selectedIds as $key => $value) {
                $data_count = 0;
                $data_count = Database::table('configurations')->where('crm_id', '=', (int)$value)->find()->count();
               
                if($data_count != 0){
                    break;
                }   
            }
            if ($data_count > 0)
            {
                return array(
                    'success' => false,
                    'data' => array(),
                    'error_message' => ($id == '') ? 'Some of selected CRM are already used in Configuration': 'Sorry! This CRM is already used in Configuration',
                );
            }
            foreach ($selectedIds as $key => $selectedId) {
                $res = Database::table($this->table['name'])->find($selectedId)->delete();
                if($res){
                    $deletedIds[] = $selectedId;
                }
                else{
                    $notDeletedIds[] = $selectedId;
                }
            }
            
            return array(
                'success' => true,
                'data' => array(),
                'deleted_ids' => $deletedIds,
                'not_deleted_ids' => $notDeletedIds 
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

    private function filterInput($key)
    {
        switch ($this->table['attr'][$key])
        {
            case 'integer':
                return Request::form()->getInt($key, 0);
            case 'boolean':
                return Request::form()->getBoolean($key, false);
            default:
                return Request::form()->get($key, '');
        }
    }

    private function isValidData($data)
    {
        if (empty($data->crm_label))
        {
            throw new Exception("CRM label is required");
        }
        if (
                empty($data->crm_type) ||
                !in_array(
                        $data->crm_type, $this->crmTypes
                )
        )
        {
            throw new Exception("CRM type is required");
        }

        $crmCredentialValidation = $this->crmValidation($data);
        if (!$crmCredentialValidation)
        {
            throw new Exception("Invalid credentials");
        }

        if ($data->crm_type == "nmi")
        {
            return $this->makeGwDbTable($data);
        }

        return true;
    }

    private function makeGwDbTable($data)
    {
        try
        {
            $sql = 'CREATE TABLE IF NOT EXISTS `'.$data->crm_type.'_datastore` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `orderId` varchar(100) DEFAULT NULL,
                `customerId` varchar(100) DEFAULT NULL,
                `step` tinyint(3) DEFAULT NULL,
                `type` varchar(20) DEFAULT NULL,
                `configId` int(11) NOT NULL,
                `crmId` int(11) NOT NULL,
                `crmType` varchar(20) NOT NULL,
                `crmPayload` text NOT NULL,
                `crmResponse` text,
                `rawPayload` text,
                `rawResponse` text,
                `created_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`)
              ) ENGINE=InnoDB DEFAULT CHARSET=latin1;';
            $factory = new ConnectionFactory();
            self::$dbConnection = $factory->make(array(
                'driver' => 'mysql',
                'host' => Config::settings('db_host'),
                'username' => Config::settings('db_username'),
                'password' => Config::settings('db_password'),
                'database' => Config::settings('db_name'),
                'charset' => 'utf8',
                'collation' => 'utf8_unicode_ci',
            ));
            if (!self::$dbConnection)
            {
                throw new Exception(
                'Couldn\'t authenticate database credentials. Please recheck your settings.'
                );
            }
            self::$dbConnection->query($sql);
            return true;
        }
        catch (Exception $ex)
        {
            throw new Exception(
            'Table could not be created. Please recheck your settings.'
            );
        }
    }

    private function crmValidation($data)
    {
        $endpointUrl = $data->endpoint;
        if (
                strpos($data->endpoint, 'http://') !== 0 &&
                strpos($data->endpoint, 'https://') !== 0
        )
        {
            $endpointUrl = 'https://' . $data->endpoint;
        }

        $credential = array(
            'endpoint' => $endpointUrl,
            'username' => $data->username,
            'password' => $data->password,
        );

        $crmClass = sprintf('Application\Model\%s', ucfirst($data->crm_type));

        return call_user_func_array(
                array($crmClass, 'isValidCredential'), array($credential)
        );
    }

}
