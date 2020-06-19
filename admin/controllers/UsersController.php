<?php

namespace Admin\Controller;

use Exception;
use Lazer\Classes\Database;
use Lazer\Classes\Helpers\Validate;
use Lazer\Classes\LazerException;
use Application\Request;
use Application\Config;
use Symfony\Component\PropertyAccess\PropertyAccess;

class UsersController
{
    private $table;

    public function __construct()
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();

        $this->table = array(
            'name' => 'users',
            'attr' => array(
                'id' => 'integer',
                'username' => 'string',
                'password'    => 'string',
                'email'    => 'string',
                'user_type' => 'integer',
                'change_access_permissions' => 'boolean',
                'ecommerce' => 'boolean',
                'campaigns' => 'boolean',
                'funnel_configurations' => 'boolean',
                'coupons' => 'boolean',
                'cms' => 'boolean',
                'extensions' => 'boolean',
                'logs' => 'boolean',
                'systems_log' => 'boolean',
                'user_activity' => 'boolean',
                'change_log' => 'boolean',
                'system' => 'boolean',
                'crm' => 'boolean',
                'users' => 'boolean',
                'settings' => 'boolean',
                'advance_settings' => 'boolean',
                'tools' => 'boolean',
                'affiliate_manager' => 'boolean',
                'pixel_manager' => 'boolean',
                'rotators' => 'boolean',
                'mid_routing' => 'boolean',
                'traffic_monitor' => 'boolean',
                'auto_responder' => 'boolean',
                'auto_filters' => 'boolean',
                'scheduler' => 'boolean',
                'diagnosis' => 'boolean',
                'split_test' => 'boolean',
                'upsell_manager' => 'boolean'
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


    public function userList()
    {

        $rows = Database::table('users')->findAll();
        $users = array();
        foreach ($rows as $row)
        {
                array_push($users, array(
                    'id' => $row->id,
                    'username' => $row->username,
                    'user_type_id' => "$row->user_type",
                    'usertype' => self::userTypeToString($row->user_type),
                    'password' => $row->password,
                    'email' => !empty($row->email) ? $row->email : '',
                ));
        }
        
        return array(
            'success' => true,
            'data'    => $users,
        );
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

    public function add(){
        try
        {
            $password = Request::get('password');

            if(strlen($password) < 8) {
                return array(
                    'success' => false,
                    'data' =>Request::get('username'),
                    'error_message' => 'Password must have at least 8 characters.',
                );
            }

            if(Request::form()->has('email')) {
                $isUsernameExists = Database::table($this->table['name'])->where('username', '=', Request::get('username'))->orWhere('email', '=', Request::get('email'))->find()->count();
            }
            else {
                $isUsernameExists = Database::table($this->table['name'])->where('username', '=', Request::get('username'))->find()->count();
            }
            
            if($isUsernameExists)
                return array(
                    'success' => false,
                    'data' =>Request::get('username'),
                    'error_message' => 'Username or email is already exists.',
                );
            
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
            $password = Request::get('password');

            if(strlen($password) < 8) {
                return array(
                    'success' => false,
                    'data' =>Request::get('username'),
                    'error_message' => 'Password must have at least 8 characters.',
                );
            }

            if(Request::form()->has('email') && strlen(Request::form()->get('email'))) {

                $isUseremailExists = Database::table($this->table['name'])->where('email', '=', Request::get('email'))->where('id', '!=', $id)->find()->count();
                if($isUseremailExists) {
                    return array(
                        'success' => false,
                        'data' =>Request::get('username'),
                        'error_message' => 'Email is already exists.',
                    );
                }
            }
            
            $row = Database::table($this->table['name'])->find($id);
            $data = array();
            $updateStatus = false;

            foreach ($this->table['attr'] as $key => $type)
            {
                if ($key === 'id' || $key === 'last_modified')
                {
                    continue;
                }

                $valueGet = $this->filterInput($key);

                if ($row->{$key} != $valueGet)
                {
                    $updateStatus = true;
                }
                $data[$key] = $row->{$key} = $valueGet;
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


    public function delete($id='')
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

    private function filterInteger($key, $valueGet)
    {
        if ($valueGet != '')
        {
            return number_format($valueGet, 2, '.', '');
        }
        return $valueGet;
    }

    private function isValidData($data)
    {
        if (empty($data->username))
        {
            throw new Exception("Username is required");
        }
        return true;
    }

    public static function userTypeToString($userTypeId)
    {
        $returnType = null;
        switch ($userTypeId) {
            case '1':
                $returnType = "Admin";
                break;
            
            case '2':
                $returnType = "Standard";
                break;

            case '3':
                $returnType = "Super Admin";
                break;
            
            default:
                $returnType = "Super Admin";    
                break;
        }

        return $returnType;
    }
    
    public static function getUserByEmailID($email)
    {
        return Database::table('users')->where('email', '=', $email)->find()->asArray();
    }
}
