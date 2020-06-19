<?php

namespace Admin\Controller;

use Application\Request;
use Database\Connectors\ConnectionFactory;
use Exception;
use Lazer\Classes\Database;
use Lazer\Classes\Helpers\Validate;
use Lazer\Classes\LazerException;
use DateTime;
use Application\Config;
use SebastianBergmann\Environment\Console;
use Symfony\Component\PropertyAccess\PropertyAccess;

class MidroutingController
{
    private $table, $database, $configTable, $csvMimes, $routerPath, $defaultGatewayPath;
    public function __construct()
    {
        $this->table              = 'routings';
        $this->configTable        = 'configurations';
        $this->database           = STORAGE_DIR . DS . 'midrouting.sqlite';
        $this->routerPath         = STORAGE_DIR . DS . 'router';
        $this->defaultGatewayPath = $this->routerPath . DS . '.default';
        $this->defaultAffliatePath = $this->routerPath . DS . '.aff';
        $this->createTable();
        $this->csvMimes = array(
            'text/x-comma-separated-values',
            'text/comma-separated-values',
            'application/octet-stream',
            'application/vnd.ms-excel',
            'application/x-csv',
            'text/x-csv',
            'text/csv',
            'application/csv',
            'application/excel',
            'application/vnd.msexcel',
            'text/plain');

        $this->accessor = PropertyAccess::createPropertyAccessor();        
        $this->routingTable = array(
            'name' => 'routings',
            'attr' => array(
                'id' => 'integer',
                'profile_name' => 'string',
                'default_gateway' => 'string',
                'enable_affiliate_posting' => 'boolean',
                'affiliate_value' => 'string',
                'affiliate_parameter' => 'string',
                'enable_gateway_based_mid_routing' => 'boolean',
                'enable_geobased_mid' => 'boolean',
                'geo_type' => 'string',
                'state' => 'string',
                'geo_gateways' => 'string',
                'step_id' => 'string',
                'bin_category' => 'string',
                'last_modified' => 'string',
                'geoLocation' => 'string'
            ),
        );

        try
        {
            Validate::table($this->routingTable['name'])->exists();
        }
        catch (LazerException $ex)
        {
            Database::create(
                    $this->routingTable['name'], $this->routingTable['attr']
            );
        }
    }
    private function createConnection()
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

    private function createTable()
    {
        try
        {
            $sql = "CREATE TABLE IF NOT EXISTS " . $this->table . " ("
                . "'id' INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,"
                . "'config_id' TEXT,"
                . "'source' TEXT DEFAULT '',"
                . "'destination' TEXT,"
                . "'urldata' TEXT,"
                . "'split' TEXT,"
                . "'exclude' TEXT,"
                . "'profile_id' TEXT,"
                . "'ip' TEXT DEFAULT '',"
                . "'created_at' TEXT,"
                . "'modified_at' TEXT )";

            $query = $this->createConnection()->query($sql);
            
            $checkColSql = "PRAGMA table_info(" .$this->table. ")";
            $cols = $this->createConnection()->fetchAll($checkColSql);
            $colsCount = count($cols);
            
            return true;
        } catch (Exception $ex) {
            return array(
                'success'       => false,
                'error_message' => $ex->getMessage(),
            );
        }
    }
    private function mergeConfigurationName($list)
    {
        foreach ($list as $listKey => $listValue) {
            $row = Database::table('configurations')->where('id', '=', $listValue['config_id'])->find()->asArray();
            if (is_array($row) && !empty($row)) {
                $list[$listKey]['config_name'] = $row[0]['configuration_label'];
            }
            if(!empty($list[$listKey]['modified_at'])){
                $source = $list[$listKey]['modified_at'];
                $date = new DateTime($source);
                $list[$listKey]['modified_at'] = $date->format('dS M Y H:i:s');
            }
            if(empty($list[$listKey]['split'])){
                $list[$listKey]['split'] = 'no';
            }
        }
        
        return $list;
    }

    public function getData($id)
    {
        try {
            $row   = array();
            $query =  $this->createConnection()->table($this->table)
                ->select('id', 'config_id', 'source', 'destination', 'ip', 'created_at', 'modified_at', 'urldata', 'split', 'exclude', 'profile_id')
                ->orderBy('id', 'desc');

            $totalRows = $this->createConnection()->table($this->table)
                ->count();
            if (Request::form()->has('offset', 'limit')) {

                $data = $query
                    ->offset(Request::form()->get('offset'))
                    ->limit(Request::form()->get('limit'))
                    ->get();
                   // $data = $this->mergeConfigurationName($data);
                return array(
                    'success'   => true,
                    'data'      => $data,
                    'totalData' => $totalRows,
                );
            }

            if ($id) {
                $query->where('profile_id', '=', $id);
            }
            $list = $query
                ->get();
            //$lists = $this->mergeConfigurationName($list);

            // print_r($list);
            return array(
                'success' => true,
                // 'data'    => ($id && !empty($list)) ? $list : $list,
                'data'    => ($id && !empty($list)) ? $list : $list,
            );
        } catch (Exception $ex) {
            return array(
                'success'       => false,
                'error_message' => $ex->getMessage(),
            );
        }

    }

    public function deleteData($id)
    {
        try {
            $query = $this->createConnection()->table($this->table)
                ->where('id', '=', $id)
                ->delete();
            return array(
                'success' => true,
                'message' => 'Deleted Successfuly',
            );
        } catch (Exception $ex) {
            return array(
                'success'       => false,
                'error_message' => $ex->getMessage(),
            );
        }
    }

    public function deleteRouting($id = '')
    {
        try
        {           
            $selectedIds = ($id == '') ? Request::get('ids') : array($id);
            $deletedIds = $notDeletedIds = [];

            foreach ($selectedIds as $key => $selectedId) {
                $res = Database::table($this->routingTable['name'])->find($selectedId)->delete();
                if($res){
                    $deletedIds[] = $selectedId;
                }
                else{
                    $notDeletedIds[] = $selectedId;
                }
            }

            return array(
                'success' => true,
                'data' => array()
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

    public function getAsssignedMidWithMultipleId()
    {
       
        $midIds = Request::form()->get('ids');

        try
        {
            $row = Database::table('configurations')->findAll()->asArray();
            $data = array();
            if (empty($row))
            {
                return array(
                    'success' => true,
                    'data' => array(),
                );
            }
            foreach ($this->routingTable['attr'] as $key => $type)
            {
                $valueGet = $this->accessor->getValue($row[0], '[' . $key . ']');
                $data[$key] = ($valueGet !== NULL) ? $valueGet : '';
            }
            $assignedConfig = array();
            $i=0;
            
            foreach ($row as $key => $value)
            {
                if (!empty($value['mid_routing_profile']))
                {
                    if((is_array($midIds))) {
                        foreach($midIds as $midId){

                            if ($midId == $value['mid_routing_profile'] && $value['crm_gateway_settings'] == 'mid_router')
                            {
                                $assignedConfig[$i] = $value;
                            }
                        } 
                    }
                    else {
                        if ($midIds == $value['mid_routing_profile'] && $value['crm_gateway_settings'] == 'mid_router')
                        {
                            $assignedConfig[$i] = $value;
                        }
                    }
                                       
                }
                $i++;
            }

           $assignedConfig = array_values($assignedConfig);
            return (empty($assignedConfig)) ? array(
                'success' => true,
                'data' => array(),
                    ) : array(
                'success' => false,
                'data' => $assignedConfig,
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
    
    public function deleteAll($profile_id)
    {
        try {
            $query = $this->createConnection()->table($this->table)->where('profile_id', '=', $profile_id)
                ->delete();
            return array(
                'success' => true,
                'message' => 'Deleted Successfuly',
            );
        } catch (Exception $ex) {
            return array(
                'success'       => false,
                'error_message' => $ex->getMessage(),
            );
        }
    }

    public function all()
    {
        try
        {
            $data = Database::table($this->routingTable['name'])->orderBy('id', 'desc')->findAll()->asArray();
            

            if(!empty($data)) {
                return array(
                    'success' => true,
                    'data'    => $data,
                );
            }

            return array(
                'success' => false,
                'data'    => $data,
                'error_message' => 'Something went wrong.'
            );

        } catch (Exception $ex) {
            return array(
                'success'       => false,
                'data'          => array(),
                'error_message' => $ex->getMessage(),
            );
        }
    }
    
    public function add($id = '')
    {
        try
        {
            $data    = Request::form()->all();
            $sources = explode(',', $data['source']);

            // $checkConfig = $this->checkConfig($data['config_id'],$data);
            // if(!empty($checkConfig['data'])){
            //     $update = $this->updateConfig($data['config_id'],$data);
            // }
            // else {
            //     die('ko');
            // foreach ($sources as $source) {
            //     if(!empty($data['urldata'])) {
            //         $data['urldata'] = $this->formatURL($data['urldata']);
            //     }
            //     $insertData = array(
            //         'config_id'   => $data['config_id'],
            //         'source'      => !empty($source) ? $source : 'NA',
            //         'destination' => $data['destination'],
            //         'ip'          => Request::getClientIp(),
            //         'created_at'  => date('Y-m-d H:i:s', time()),
            //         'modified_at' => date('Y-m-d H:i:s', time()),
            //         'urldata'     => !empty($data['urldata']) ? $data['urldata'] : 'NA',
            //         'split'       => !empty($data['split']) ? $data['split'] : 'no',
            //         'exclude'     => !empty($data['exclude']) ? $data['exclude'] : 'no',
            //         'profile_id'  => $nextId,
            //     );

            //     print_r($insertData);
                
            //     $this->insert($insertData);
            // }  
                             
            // }

            $nextId = $id !== '' ? $id : Database::table($this->routingTable['name'])->lastId() + 1;
            foreach ($sources as $source) {
                if(!empty($data['urldata'])) {
                    $data['urldata'] = $this->formatURL($data['urldata']);
                }
                $insertData = array(
                    'config_id'   => $data['config_id'],
                    'source'      => !empty($source) ? $source : 'NA',
                    'destination' => $data['destination'],
                    'ip'          => Request::getClientIp(),
                    'created_at'  => date('Y-m-d H:i:s', time()),
                    'modified_at' => date('Y-m-d H:i:s', time()),
                    'urldata'     => !empty($data['urldata']) ? $data['urldata'] : 'NA',
                    'split'       => !empty($data['split']) ? $data['split'] : 'no',
                    'exclude'     => !empty($data['exclude']) ? $data['exclude'] : 'no',
                    'profile_id'  => $nextId,
                );

                $this->insert($insertData);
            }  

            return array(
                'success' => true,
                'data'    =>  $insertData,
            );
        } catch (Exception $ex) {
            return array(
                'success'       => false,
                'error_message' => $ex->getMessage(),
            );
        }
    }
    
    private function checkConfig($id, $data)
    {
        try {
            $data = $this->createConnection()->table($this->table)
                        ->where('config_id', '=', $id)
                        ->where('source', '=', $data['source'])
                        ->get();
            
            return array(
                'success'   => true,
                'data'      => $data,
            );
        } catch (Exception $ex) {
            throw ($ex);
        }
    }
    
    private function updateConfig($id, $data)
    {
        try {
            $this->createConnection()->table($this->table)
                ->where('config_id', '=', $id)
                ->where('source', '=', $data['source'])
                ->update($data);
            return;
        } catch (Exception $ex) {
            throw ($ex);
        }
    }
    
    private function deleteMidRec($id)
    {
        $this->createConnection()->table($this->table)
                ->where('id', '=', $id)
                ->delete();
    }

    public function edit($id = '')
    {
        try {
            $data = Request::form()->all();
            unset($data['config_name'], $data['id']);
            $sources = explode(',', $data['source']);
            $this->deleteMidRec($id);
            foreach ($sources as $source) {
                if(!empty($data['urldata'])) {
                    $data['urldata'] = $this->formatURL($data['urldata']);
                }
                $data['source'] = !empty($source) ? $source : 'NA';
                $data['config_id'] = !empty($data['config_id']) ? $data['config_id'] : 'NA';
                $data['urldata'] = !empty($data['urldata']) ? $data['urldata'] : 'NA';
                $data['split'] = !empty($data['split']) ? $data['split'] : 'no';
                $data['exclude'] = !empty($data['exclude']) ? $data['exclude'] : 'NA';
                $data['created_at'] = date('Y-m-d H:i:s', time());
                $data['modified_at'] = date('Y-m-d H:i:s', time());
                $data['ip' ] = Request::getClientIp();
                $data['destination'] = $data['destination'];
                $this->insert($data);
            }
            
            return array(
                'success' => true,
                'data'    => true,
            );
        } catch (Exception $ex) {
            return array(
                'success'       => false,
                'error_message' => $ex->getMessage(),
            );
        }
    }

    public function createOrEdit($id = '')
    {
        $data = Request::form()->all();

        $testCaseData = $this->maxMid()['data'];
        if($data['id'] > $testCaseData) {
            // New Row
            return $this->add($id);
        }
        else {
            // Edit row
            return $this->edit($data['id']);
        }
    }

    public function maxMid()
    {
        return array(
            'success' => true,
            'data'    => $this->createConnection()->table($this->routingTable['name'])->max('id')
        );
    }
    private function insert($data)
    {
        try {
            $this->createConnection()->table($this->table)->insert($data);
            return;
        } catch (Exception $ex) {
            throw ($ex);
        }

    }
    private function update($id, $data)
    {
        try {
            $this->createConnection()->table($this->table)
                ->where('id', '=', $id)
                ->update($data);
            return;
        } catch (Exception $ex) {
            throw ($ex);
        }
    }

    private function deleteConfigs($cofigID)
    {
        try {
            if ($cofigID) {
                $this->createConnection()->table($this->table)
                    ->where('config_id', '=', $cofigID)
                    ->delete();
            }

            return;
        } catch (Exception $ex) {
            throw ($ex);
        }

    }
  
    private function detectDelimiter($fh)
    {
        $delimiters = ["\t", ";", "|", ","];
        $data_1     = null;
        $data_2     = null;
        $delimiter  = $delimiters[0];
        foreach ($delimiters as $d) {
            $data_1 = fgetcsv($fh, 4096, $d);
            if (sizeof($data_1) > sizeof($data_2)) {
                $delimiter = sizeof($data_1) > sizeof($data_2) ? $d : $delimiter;
                $data_2    = $data_1;
            }
            rewind($fh);
        }

        return $delimiter;
    }

    public function csvColCount()
    {
        try {

            if (!empty($_FILES['file']['name']) && in_array($_FILES['file']['type'], $this->csvMimes)) {
                if (is_uploaded_file($_FILES['file']['tmp_name'])) {

                    //open uploaded csv file with read only mode
                    $csvFile = fopen($_FILES['file']['tmp_name'], 'r');

                    $delimiter = $this->detectDelimiter($csvFile);

                    $col_header_array = fgetcsv($csvFile, 1000, $delimiter);

                    //skip first line
                    $column = count(fgetcsv($csvFile, 1000, $delimiter));

                    if (is_array($col_header_array) && !empty($col_header_array)) {
                        return array(
                            'success' => true,
                            'data'    => $col_header_array,
                        );
                    }

                }
            }
        } catch (Exception $ex) {
            return array(
                'success'       => false,
                'error_message' => $ex->getMessage(),
            );
        }
    }

    public function csvUpload( $id = '')
    {
        try {
            if (!empty($_FILES['file']['name']) && in_array($_FILES['file']['type'], $this->csvMimes)) {
                if (is_uploaded_file($_FILES['file']['tmp_name'])) {

                    //open uploaded csv file with read only mode
                    $csvFile = fopen($_FILES['file']['tmp_name'], 'r');

                    $config_id = Request::form()->get('config_id');

                    //$this->deleteConfigs($config_id);

                    $delimiter = $this->detectDelimiter($csvFile);
                    //skip first line
                    fgetcsv($csvFile);
                    $source_column      = Request::form()->get('source');
                    $source_destination = Request::form()->get('destination');
                    $source_url         = Request::form()->get('urldata');
                    $split_data         = Request::form()->get('split');
                    $exclude_Data       = Request::form()->get('exclude');
                    
                    //parse data from csv file line by line
                    while (($line = fgetcsv($csvFile, 4096, $delimiter)) !== false) {

                        $source_gateways = $line[$source_column];
                        $destination_ids = $line[$source_destination];
                        $url             = $line[$source_url];
                        $split           = $line[$split_data];
                        $exclude         = $line[$exclude_Data];
                        
                        $source_gateway_array = explode(',', $source_gateways);

                        $nextId = $id !== '' ? $id : Database::table($this->routingTable['name'])->lastId() + 1; 
                        foreach ($source_gateway_array as $source_id) {
                            $source_id = trim($source_id);
                            if (!empty($destination_ids)) {
                                if(!empty($url)) {
                                    $url = $this->formatURL($url);
                                }
                                
                                $insertData = array(
                                    'config_id'   => $config_id,
                                    'source'      => !empty($source_id) ? $source_id : 'NA',
                                    'destination' => $destination_ids,
                                    'urldata'     => !empty($url) ? $url : 'NA',
                                    'split'       => !empty($split) ? $split : 'no',
                                    'exclude'     => !empty($exclude) ? $exclude : 'NA',
                                    'profile_id'  => $nextId,
                                    'ip'          => Request::getClientIp(),
                                    'created_at'  => date('Y-m-d H:i:s', time()),
                                    'modified_at' => date('Y-m-d H:i:s', time()),
                                );
                                $this->insert($insertData);
                            }

                        }

                    }
                    //close opened csv file
                    fclose($csvFile);

                    $result = $this->getData($nextId);
                    return array(
                        'success' => true,
                        'data'    => $result,
                    );
                }
            }

        } catch (Exception $ex) {
            return array(
                'success'       => false,
                'error_message' => $ex->getMessage(),
            );
        }
    }
    public function defaultGateway()
    {
        try {
            $gateway = Request::form()->get('gateway');
            if (!file_exists($this->routerPath)) {
                mkdir($this->routerPath);
            }
            if (!is_writable($this->routerPath)) {
                throw new Exception("File is not writable");
            }

            if (file_exists($this->defaultGatewayPath) && !is_writable($this->defaultGatewayPath)) {
                throw new Exception("File is not writable");
            }
            file_put_contents($this->defaultGatewayPath, $gateway, LOCK_EX);
            return array(
                'success' => true,
                'data'    => $gateway,
                'message' => 'Default Gateway saved successfully',
            );
        } catch (Exception $ex) {
            return array(
                'success'       => false,
                'error_message' => $ex->getMessage(),
            );
        }

    }
    
    public function saveAffiliates()
    {
        try {
            $affiliates = Request::form()->get('affiliates');
            $enableAffiliates = Request::form()->get('enable_affiliate_posting');
            $enableSplit = Request::form()->get('enable_split_routing');
            $data = array(
                        'affiliates' => $affiliates,
                        'enableAffiliates' => $enableAffiliates,
                        'enableSplit' => $enableSplit
                    );
            
            $jsonData = json_encode($data);
            
            if (!file_exists($this->routerPath)) {
                mkdir($this->routerPath);
            }
            if (!is_writable($this->routerPath)) {
                throw new Exception("File is not writable");
            }

            if (file_exists($this->defaultAffliatePath) && !is_writable($this->defaultAffliatePath)) {
                throw new Exception("File is not writable");
            }
            file_put_contents($this->defaultAffliatePath, $jsonData, LOCK_EX);
            $data = json_decode($jsonData);
            return array(
                'success' => true,
                'data'    => $data,
                'message' => 'Saved successfully',
            );
        } catch (Exception $ex) {
            return array(
                'success'       => false,
                'error_message' => $ex->getMessage(),
            );
        }

    }
    
    public function getAffiliates()
    {
        $data = '';
        if (file_exists($this->defaultAffliatePath)) {
            $data = file_get_contents($this->defaultAffliatePath);
        }
        $jsonData = json_decode($data);
        
        return array(
            'success' => true,
            'data'    => $jsonData,
        );
    }
    
    public function getDefaultGateway()
    {
        $data = '';
        if (file_exists($this->defaultGatewayPath)) {
            $data = file_get_contents($this->defaultGatewayPath);
        }
        return array(
            'success' => true,
            'data'    => $data,
        );
    }
    
    public function checkExtensions()
    {
        $result = array(
            'success' => true,
            'extensionMidRoutingActive' => false,
        );
		
        $extensions = Config::extensions();
		
        foreach ($extensions as $extension)
        {
            if ($extension['extension_slug'] !== 'MidsRouting')
            {
                continue;
            }
            if ($extension['active'] === true)
            {
                $result['extensionMidRoutingActive'] = true;
            }
            break;
        }


        return $result;
    }
    
    private function formatURL($urldata)
    {
        return preg_replace('#^https?://#', '', $urldata);
    }

    public function getMid($id = '')
    {
        try
        {
            $row = Database::table($this->routingTable['name'])->where('id', '=', $id)->find()->asArray();
            $data = array();
            if (empty($row))
            {
                return array(
                    'success' => false,
                    'data' => array(),
                );
            }
            foreach ($this->routingTable['attr'] as $key => $type)
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
    public function saveMid()
    {
        try
        {
            $row  = Database::table($this->routingTable['name']);
            $data = array();
            foreach ($this->routingTable['attr'] as $key => $type) {
                if ($key === 'id') {
                    continue;
                }

                if ($key == 'last_modified')
                {
                    $valueGet = date("Y-m-d H:i:s");
                }
                else {

                    $valueGet = $this->filterInteger($key, $this->filterInput($key));
                }
                
                $data[$key] = $row->{$key} = $valueGet;
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

    public function editMid($id = '')
    {
        try
        {
            $row  = Database::table($this->routingTable['name'])->find($id);
            $data = array();
            foreach ($this->routingTable['attr'] as $key => $type) {
                if ($key === 'id') {
                    continue;
                }
                $valueGet = $this->filterInput($key);
                
                $data[$key] = $row->{$key} = $valueGet;
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

    private function filterInput($key)
    {
        
        switch ($this->routingTable['attr'][$key]) {
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

    private function filterInteger($key, $valueGet)
    {
        if (($key == 'shipping_price' || $key == 'product_price' || $key == 'rebill_product_price') && $valueGet != '')
        {
            return number_format($valueGet, 2, '.', '');
        }
        return $valueGet;
    }
}
