<?php

namespace Admin\Controller;

use Exception;
use Application\Cron;
use Application\Request;
use Application\Session;
use Lazer\Classes\Database;
use Lazer\Classes\Helpers\Validate;
use Lazer\Classes\LazerException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use function GuzzleHttp\json_encode;
use Database\Connectors\ConnectionFactory;

class CronController
{
    private static $logTable = 'scheduler_log';
    private static $logDatabase = STORAGE_DIR . DS . 'cronlogs.sqlite';
    private static $logStoreInDatabase = true; // if false then will store in data.json file.
	public function __construct()
	{
        $this->accessor = PropertyAccess::createPropertyAccessor();
        
        // If Table name is require to change, there some places where table name use static manner , update those.
		$this->table = array(
            'name' => 'crons',
            'attr' => array(
                'id' => 'integer',
                "handler" => 'string',
				"every" => 'string',
				"last_modified" => 'string',
				"created_at" => "string",
				"priority" => "integer",
                "status" => "boolean",
                "extension_slug" => "string"
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

	public function cronList()
	{
		try
		{
			$list = Cron::getAssignedSchedules();
			return array(
				'success' => true,
				'data' => $list
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

	public function all()
    {
        try
        {
            $orderByField = Request::form()->get('orderByField');
            $orderBy = Request::form()->get('orderBy');

            if (empty($orderByField) || empty($orderBy))
            {
                $orderByField = 'priority';
                $orderBy = 'ASC';
            }


            $query = Database::table($this->table['name'])
                    ->orderBy($orderByField, $orderBy);


            $totalRows = Database::table($this->table['name'])
                            ->findAll()->count();

            if (Request::form()->get('limit') == 'all')
            {
                $data = $query
                                ->findAll()->asArray();
            }
            else if (Request::form()->has('offset', 'limit'))
            {
                $data = $query
                                ->limit(Request::form()->get('limit'), Request::form()->get('offset'))
                                ->findAll()->asArray();
            }
            else
            {
                $data = $query
                                ->findAll()->asArray();
            }
            
            return array(
                'success' => true,
                'data' => $data,
                'totalData' => (int) $totalRows,
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
            $handler = $this->filterInput('handler');
            $checkIfExistCron = Database::table($this->table['name'])->where('handler', "=", $handler)->find();

            if($checkIfExistCron->count()){
                return array(
                    'success' => false,
                    'data' => null,
                    'error_message' => 'Handler is already exist.',
                );
            }


            $row = Database::table($this->table['name']);
            $data = array();
            foreach ($this->table['attr'] as $key => $type)
            {
                if ($key === 'id')
                {
                    continue;
                }
                if ($key == 'last_modified')
                {
                    $valueGet = date("Y-m-d H:i:s");
				}
				elseif($key == 'created_at') {

					$valueGet = date("Y-m-d H:i:s");
				}
				elseif($key == 'priority') {
                    $valueGet = 0;
                    $last_priority = Database::table($this->table['name'])->orderBy($key, 'DESC')->findAll()->asArray();
                    
                    if(!empty($last_priority)){
                        $valueGet = $last_priority[0]['priority'] + 1;
                    }
				}
				elseif ($key == 'status') {
					$valueGet = $this->filterInput($key) !== null ? $this->filterInput($key) : false;
				}
                else
                {
                    $valueGet = $this->filterInput($key);
                }

                $data[$key] = $row->{$key} = $valueGet;
            }
            if ($this->isValidData($row))
            {
                $row->save();
                $data['id'] = $row->id;
                self::updateRegistry();
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
    
    // This function will insert cron into crons.data.json, if record exist then change the status to true;
    public function import($crons = array(), $slug)
    {

        if(!is_array($crons) || !array_key_exists('every', $crons) || !array_key_exists('handler', $crons) || !isset($slug)) {
            return array(
                'success' => false,
                'data' => null,
            );;
        }
                
        try
        {
            $handler = $crons['handler'];
            $checkIfExistCron = Database::table($this->table['name'])->where('handler', "=", $handler)->where('extension_slug', '=', $slug)->find();

            if($checkIfExistCron->count()){
                if(!$checkIfExistCron->status){
                    $checkIfExistCron->status = true;
                    $checkIfExistCron->save();
                }
                return;
            }

            $row = Database::table($this->table['name']);
            $data = array();
            foreach ($this->table['attr'] as $key => $type)
            {
                if ($key === 'id')
                {
                    continue;
                }
                if ($key == 'last_modified')
                {
                    $valueGet = date("Y-m-d H:i:s");
				}
				elseif($key == 'created_at') {

					$valueGet = date("Y-m-d H:i:s");
				}
				elseif($key == 'priority') {
                    $valueGet = 0;
                    $last_priority = Database::table($this->table['name'])->orderBy($key, 'DESC')->findAll()->asArray();
                    
                    if(!empty($last_priority)){
                        $valueGet = $last_priority[0]['priority'] + 1;
                    }
				}
				elseif ($key == 'status') {
					$valueGet = true;
                }
                elseif ($key == 'extension_slug'){
                    $valueGet = $slug;
                }
                else
                {
                    switch ($this->table['attr'][$key]) {
                        case 'integer':
                             $valueGet = (int)$crons[$key];
                        case 'boolean':
                             $valueGet = $crons[$key] == 'true' ? true : false;
                        default:
                            $valueGet = $crons[$key];
                    }
                    $valueGet = $crons[$key];
                }

                $data[$key] = $row->{$key} = $valueGet;
            }

            if ($this->isValidData($row))
            {
                $row->save();
                $data['id'] = $row->id;
                self::updateRegistry();
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
            $row  = Database::table($this->table['name'])->find($id);
            $data = array();
            foreach ($this->table['attr'] as $key => $type) {
                if ($key === 'id' || $key === 'created_at' || $key === 'priority' || $key === 'status') {
                    continue;
				}
				
                $valueGet = $this->filterInput($key);
               
                $data[$key] = $row->{$key} = $valueGet;
            }
            if ($this->isValidData($row)) {
                $row->save();
                $data['id'] = $row->id;
                self::updateRegistry();
                return array(
                    'success' => true,
                    'data'    => $data,
                );
            }
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
        if (empty($data->handler) || empty($data->every))
        {
            throw new Exception("All fields are required");
        }

        return true;
    }
	
	public function delete($id = '')
    {
        try
        {
            $selectedIds = ($id == '') ? Request::get('ids') : array($id);
            $deletedIds = $notDeletedIds = [];

            foreach ($selectedIds as $key => $selectedId) {
                $res = Database::table($this->table['name'])->find($selectedId)->delete();

                if($res){
                    $deletedIds[] = $selectedId;
                }
                else{
                    $notDeletedIds[] = $selectedId;
                }
            }
            $this->resetPriority();
            self::updateRegistry();
            return array(
                'success' => true,
                'data'    => array(),
            );
            
        } catch (Exception $ex) {
            return array(
                'success'       => false,
                'data'          => array(),
                'error_message' => $ex->getMessage(),
            );
        }
    }

    public function deleteCronBySlugName($slug)
    {
        if(!isset($slug)) {
            return array(
                'success' => false,
                'data'    => array(),
            );
        }

        try
        {
            $res = Database::table($this->table['name'])->where('extension_slug' , '=', $slug)->delete();
            $this->resetPriority();
            self::updateRegistry();
            return array(
                'success' => true,
                'data'    => array(),
            );
            
        } catch (Exception $ex) {
            return array(
                'success'       => false,
                'data'          => array(),
                'error_message' => $ex->getMessage(),
            );
        }
    }

    public function priorityUp($id, $priority)
    {
        try
        {
            // 1. Findout the below priority id
            // 2. reduce the current id priority
            // 3. increase the below priority
            $row  = Database::table($this->table['name'])->find($id);
            $below_priority_row = Database::table($this->table['name'])->where('priority', "=", ($priority - 1))->find();

            $row->priority = $priority - 1;
            $below_priority_row->priority = $priority - 0;

            $row->save(); $below_priority_row->save();
            self::updateRegistry();
            return array(
                'success' => true,
                'message'    => 'Priority change successfully done.',
            );

        } catch (Exception $ex) {
            return array(
                'success'       => false,
                'data'          => array(),
                'error_message' => $ex->getMessage(),
            );
        }
    }

    public function priorityDown($id, $priority)
    {
        try
        {
            // 1. Findout the below priority id
            // 2. reduce the current id priority
            // 3. increase the below priority
            $row  = Database::table($this->table['name'])->find($id);
            $below_priority_row = Database::table($this->table['name'])->where('priority', "=", ($priority + 1))->find();

            $row->priority = $priority + 1;
            $below_priority_row->priority = $priority - 0;

            $row->save(); $below_priority_row->save();

            self::updateRegistry();
            return array(
                'success' => true,
                'message'    => 'Priority change successfully done.',
            );

        } catch (Exception $ex) {
            return array(
                'success'       => false,
                'data'          => array(),
                'error_message' => $ex->getMessage(),
            );
        }
    }

    public function status($id = '' , $status = '') 
    {
      
        $selectedIds = empty($id) ? Request::get('ids') : array($id);
        $status =  $status === '' ? Request::get('status') : $status === 'true'? true: false;

        try
        {
            foreach ($selectedIds as $key => $selectedId) {

                $res = Database::table($this->table['name'])->find($selectedId);
                $res->status =  $status;

                if($this->isValidData($res)){

                    $res->save();
                    $changeStatusId[] = $selectedId;
                }
                else{
                    $notchangeStatusId[] = $selectedId;
                }
            }

            $msg = $status ? 'activate' : 'deactivate';

            if (!empty($changeStatusId))
            {
                self::updateRegistry();
                return array(
                    'success' => true,
                    'data' => $changeStatusId,
                    'success_message' => sprintf('Scheduler has been %s successfully.', $msg)
                );
            }
            else {
                return array(
                    'success' => false,
                    'data' => null,
                    'error_message' => sprintf('System error, could not %s Scheduler%s.', $msg, count($selectedIds) > 2 ? 's' : '')
                );
            }
        }
        catch (Exception $ex)
        {
            return array(
                'success' => false,
                'data' => null,
                'error_message' => $ex->getMessage(),
            );
        }
    }

    public static function getCronIdByExtensionSlug($slug)
    {
        try
        {
            $rows = Database::table('crons')->where('extension_slug', '=', $slug)->findAll()->asArray();
            $data = array();
            if (empty($rows))
            {
                return false;
            }
            foreach($rows as $row) {
                array_push($data, $row['id']);
            }
            return $data;
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

    public static function getCronsReadyForRegistry()
    {
        try
        {
            $orderByField = 'priority';
            $orderBy = 'ASC';            
            $query = Database::table('crons')->where('status', "=", 'true')->groupBy('extension_slug')->orderBy($orderByField, $orderBy);
            $crons = $query->findAll()->asArray();
            foreach($crons as $category => $value) {
                
                if($category == "") {
                    $crons['CustomCrons'] = $value;
                    unset($crons[$category]);
                }
            }
            if(!empty($crons)) return $crons;
            return false;
        }
        catch (Exception $ex)
        {
            return false;
        }
    }

    public static function updateRegistry($replace = false)
    {
        return;
        $filePath = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'storage/registry.json';
        // get the registry file
        $registryFile = file_get_contents($filePath);
        $registry = json_decode($registryFile, true);
        $customCrons = self::getCronsReadyForRegistry();
        if($customCrons) $registry['crons'] = array_merge($registry['crons'], self::getCronsReadyForRegistry());
        if(!$replace) 
            return ($registry);
        else
            file_put_contents($filePath, json_encode($registry), LOCK_EX);
    }

    public static function fetchCronByPriorityForExecution()
    {
        try
        {
            $orderByField = 'priority';
            $orderBy = 'ASC';
            $response = array();
            
            $query = Database::table('crons')->where('status', "=", 'true')->orderBy($orderByField, $orderBy);
            $crons = $query->findAll()->asArray();
            foreach($crons as $cron) {
                array_push($response, array(
                    'id' => $cron['id'],
                    'every' => $cron['every'],
                    'handler' => $cron['handler'],
                    'overlap' => false
                ));                
            }
            if(!empty($response)) return $response;
            return false;
        }
        catch (Exception $ex)
        {
            return false;
        }
    }

    private function resetPriority()
    {
        try
        {
			$rows = Database::table($this->table['name'])->orderBy('priority', 'ASC')->findAll()->asArray();
            $data = array();
            if (empty($rows))
            {
                return false;
            }
            $priority = 0;
            foreach ($rows as $row)
            {
               $needToUpdateRow = Database::table($this->table['name'])->where('id', '=', $row['id'])->find();
               if($needToUpdateRow->count()) {
                    $needToUpdateRow->priority = $priority++;
                    $needToUpdateRow->save();
               }
            }
            return true;
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

    private static function createConnection()
    {
        $database = self::$logDatabase;
        try
        {
            if (!extension_loaded('pdo_sqlite')) {
                throw new Exception('PDO extension does not exists');
            }
            if (!file_exists($database)) {
                file_put_contents($database, '');
            }
            if (!is_writable($database)) {
                throw new Exception('Check write permission of database file');
            }
            $factory    = new ConnectionFactory();
            $connection = $factory->make(array(
                'driver'   => 'sqlite',
                'database' => $database,
            ));
            return $connection;
        } catch (Exception $ex) {
             throw($ex);
        }
    }

    private static function createTable()
    {
        try
        {
            $table = self::$logTable;
            $sql = "CREATE TABLE IF NOT EXISTS " . $table . " ("
                . "'id' INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,"
                . "'cron_id' TEXT,"
                . "'handler' TEXT,"
                . "'event_at' TEXT,"
                . "'event_type' TEXT,"
                . "'output' TEXT)";

            $query = self::createConnection()->query($sql);
            $checkColSql = "PRAGMA table_info(" . $table . ")";
            $cols = self::createConnection()->fetchAll($checkColSql);
            $colsCount = count($cols);
            return true;
            
        } catch (Exception $ex) {
            return array(
                'success'       => false,
                'error_message' => $ex->getMessage(),
            );
        }
    }

    public static function createLog($id, $handler, $time, $eventType, $response)
    {
        if(!isset($id) || !isset($handler) || !isset($time) || !isset($eventType) || !isset($response)) {
            return false;
        }

        try
        {
            self::createTable();
            $data = array(
                'cron_id' => (string) $id,
                'handler' =>  $handler,
                'event_at' => $time,
                'event_type' => $eventType,
                'output' => $response
            );

            self::createConnection()->table(self::$logTable)->insert($data);
            $data['id'] = self::createConnection()->table(self::$logTable)->insertGetId($data);
            return $data;
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

    public function getLog($id, $handler)
    {
        if(!isset($id) || !isset($handler)) {
            return array(
                'success' => false,
                'data' => array(),
                'error_message' => 'Id and Handler is missing.',
            );
        }

        try
        {
            self::createTable();
            $orderByField = 'id';
            $orderBy = 'DESC';
            $handler = implode('\\', explode('%7C', $handler));
           
            // from sqllite
            $querySqlLite =  self::createConnection()->table(self::$logTable)
            ->select('*')
            ->where('cron_id', "=", $id)
            ->where('handler', "=", $handler)
            ->orderBy($orderByField, $orderBy)
            ->limit(3);
            
            $cronsLogs = $querySqlLite->get();

            if(!empty($cronsLogs)) {
                return array(
                    'success' => true,
                    'data' => $cronsLogs
                );
            }
            else {
                return array(
                    'success' => false,
                    'data' => array(),
                    'error_message' => 'No Logs found.',
                );
            }
        }
        catch (Exception $ex)
        {
            return array(
                'success' => false,
                'data' => array(),
                'error_message' => $ex->getMessage()
            );
        }
    }

    public function validateHandler($handler)
    {
        $root = dirname(dirname(__DIR__)) . DS;

        if(!preg_match('/\.php$/i', $handler)) {
            return array(
                'success' => true,
                'data' => array(),
            );
        }
        else if(file_exists($root . $handler) && !strcmp(pathinfo($root . $handler, PATHINFO_EXTENSION), 'php')) {
            return array(
                'success' => true,
                'data' => array(),
            );
        }

        return array(
            'success' => false,
            'data' => array(),
            'error_message' => 'Scheduler file does not exist',
        );
    }
}
