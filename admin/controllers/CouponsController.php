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

class CouponsController
{

    private $table;
    private static $dbConnection = null;

    public function __construct()
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
        $this->table = array(
            'name' => 'coupons',
            'attr' => array(
                'id' => 'integer',
                "coupon_label" => 'string',
                "coupon_descrription" => 'string',
                "coupon_type" => 'string',
                "discount_type" => 'string',
                "coupon_value" => 'integer',
                "enable_coupon_use_limits" => 'boolean',
                "coupon_use_times" => 'string',
                "enable_coupon_expiry_date" => 'boolean',
                "coupon_start_date" => 'string',
                "coupon_end_date" => 'string',
                "applied_on" => 'string',
                "coupon_amt" => 'string',
                "coupon_code" => 'string'
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

    public static function getDatabaseConnection()
    {
        if (self::$dbConnection === null)
        {
            try
            {
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
            }
            catch (Exception $ex)
            {
                Alert::insertData(array(
                    'identifier' => 'Membership User',
                    'text' => 'Please check your database credential',
                    'type' => 'error',
                    'alert_handler' => 'extensions',
                ));
                return false;
            }
        }
        return self::$dbConnection;
    }

    public function all($campaignType = '')
    {
        try
        {
            $orderByField = Request::form()->get('orderByField');
            $orderBy = Request::form()->get('orderBy');

            if (empty($orderByField) || empty($orderBy))
            {
                $orderByField = 'id';
                $orderBy = 'DESC';
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

            if (!empty($data))
            {
                foreach ($data as $key => $dataValue)
                {
                    $last_modified_formated = isset($dataValue['last_modified']) ? date('l d F Y h:i A', strtotime($dataValue['last_modified'])) : '';
                    $data[$key]['last_modified_formated'] = $last_modified_formated;
                    $data[$key]['discount_type'] = $this->discountTypeStringify($dataValue['discount_type']);
                    $data[$key]['coupon_code'] = str_replace("\n", ",", $data[$key]['coupon_code']);
                }
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
                if ($key == 'last_modified')
                {
                    $valueGet = date("Y-m-d H:i:s");
                }
                else
                {

                    $valueGet = $this->filterInteger($key, $this->filterInput($key));
                }

                $data[$key] = $row->{$key} = $valueGet;
            }

            if (!$this->getUniqueCode($row->coupon_code))
            {
                return array(
                    'success' => false,
                    'data' => $data,
                    'error_message' => 'Coupon code already exist',
                );
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
            $updateStatus = false;
            foreach ($this->table['attr'] as $key => $type)
            {
                if ($key === 'id' || $key === 'last_modified')
                {
                    continue;
                }
                $valueGet = $this->filterInteger($key, $this->filterInput($key));
                if ($row->{$key} != $valueGet)
                {
                    $updateStatus = true;
                }
                $data[$key] = $row->{$key} = $valueGet;
            }


            if (!$this->getUniqueCode($row->coupon_code, $row->id))
            {
                return array(
                    'success' => false,
                    'data' => $data,
                    'error_message' => 'Coupon code already exist',
                );
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
        if (($key == 'shipping_price' || $key == 'product_price' || $key == 'rebill_product_price') && $valueGet != '')
        {
            return number_format($valueGet, 2, '.', '');
        }
        return $valueGet;
    }

    private function isValidData($data)
    {
        if (empty($data->coupon_label) || empty($data->coupon_type))
        {
            throw new Exception("All fields are required");
        }

        return true;
    }

    private function getUniqueCode($requestCoupon_code, $id = 0)
    {
        try
        {
            if (!empty($id))
            {
                $row = Database::table($this->table['name'])->where('coupon_code', '=', $requestCoupon_code)->where('id', '!=', $id)->find()->asArray();
            }
            else
            {
                $row = Database::table($this->table['name'])->where('coupon_code', '=', $requestCoupon_code)->find()->asArray();
            }


            return (empty($row)) ? true : false;
        }
        catch (Exception $ex)
        {
            return false;
        }
    }

    public function checkExtensions()
    {
        $result = array(
            'success' => true,
            'extensionCouponsActive' => false,
        );
        $extensions = Config::extensions();

        $extensions = Config::extensions();
        foreach ($extensions as $extension)
        {
            if ($extension['extension_slug'] !== 'Coupons')
            {
                continue;
            }
            if ($extension['active'] === true)
            {
                $result['extensionCouponsActive'] = true;
            }
            break;
        }


        return $result;
    }

    public function getCouponUser($id = '')
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
            $usedcouponData = $this->getUsedCouponUser($data['coupon_code']);

            if (empty($usedcouponData))
            {
                return array(
                    'success' => false,
                    'data' => array(),
                    'error_message' => 'No data found',
                );
            }

            $finalData = [];
            foreach ($usedcouponData as $key => $value)
            {
                $finalData[$key] = $value;
                $finalData[$key]['status'] = (!empty($value['status'])) ? true : false;
                $finalData[$key]['coupon_use_times'] = (int) $data['coupon_use_times'];
                $finalData[$key]['coupon_details'] = $data;
            }

            return array(
                'success' => true,
                'data' => $finalData,
                'totalData' => count($finalData),
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

    private function getUsedCouponUser($couponCode)
    {
        self::$dbConnection = $this->getDatabaseConnection();
        $usedCouponTbl = Config::extensionsConfig('Coupons.table_name');
        return $usedCouponDetails = self::$dbConnection->table($usedCouponTbl)->select('*')
                ->where('used_coupon_code', $couponCode)
                ->get();
    }

    public function updateUserCouponDetails()
    {

        try
        {
            $this->updateUserCouponStatus();
            $row = Database::table($this->table['name'])->find(Request::form()->get('id'));
            $row->id = (int) Request::form()->get('id');
            $row->coupon_use_times = Request::form()->get('couponLimit');
            $row->save();
            $data = array();
            return array(
                'success' => true
            );
        }
        catch (Exception $ex)
        {
            return array(
                'success' => false,
                'error_message' => $ex->getMessage(),
            );
        }
    }

    public function updateUserCouponStatus()
    {
        self::$dbConnection = $this->getDatabaseConnection();
        $usedCouponTbl = Config::extensionsConfig('Coupons.table_name');
        $query = self::$dbConnection->table($usedCouponTbl);
        $query->where('id', Request::form()->get('userCouponID'));
        $query->update(['status' => Request::form()->get('status')]);
        return true;
    }

    public function getAsssignedCoupon($couponID = 0)
    {
        try
        {
            $row = Database::table('campaigns')->findAll()->asArray();
            $data = array();
            if (empty($row))
            {
                return array(
                    'success' => true,
                    'data' => array(),
                );
            }
            foreach ($this->table['attr'] as $key => $type)
            {
                $valueGet = $this->accessor->getValue($row[0], '[' . $key . ']');
                $data[$key] = ($valueGet !== NULL) ? $valueGet : '';
            }
            $assignedCampaign = array();
            $i=0;

            foreach ($row as $key => $value)
            {
                if (!empty($value['coupon_ids']))
                {
                    $coupon_ids = json_decode($value['coupon_ids'], true);
                    if (is_array($coupon_ids) && (in_array($couponID, $coupon_ids)))
                    {
                        $assignedCampaign[$i] = $value;
                    }
                }
                $i++;
            }
           $assignedCampaign = array_values($assignedCampaign);
            return (empty($assignedCampaign)) ? array(
                'success' => true,
                'data' => array(),
                    ) : array(
                'success' => false,
                'data' => $assignedCampaign,
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

    public function getAsssignedCouponWithMultipleId()
    {
       
        $couponIDs = Request::form()->get('ids');

        try
        {
            $row = Database::table('campaigns')->findAll()->asArray();
            $data = array();
            if (empty($row))
            {
                return array(
                    'success' => true,
                    'data' => array(),
                );
            }
            foreach ($this->table['attr'] as $key => $type)
            {
                $valueGet = $this->accessor->getValue($row[0], '[' . $key . ']');
                $data[$key] = ($valueGet !== NULL) ? $valueGet : '';
            }
            $assignedCampaign = array();
            $i=0;
            
            foreach ($row as $key => $value)
            {
                if (!empty($value['coupon_ids']))
                {
                    $coupon_ids = json_decode($value['coupon_ids'], true);

                    foreach($couponIDs as $couponID){

                        if (is_array($coupon_ids) && (in_array($couponID, $coupon_ids)))
                        {
                            $assignedCampaign[$i] = $value;
                        }
                    }                    
                }
                $i++;
            }
           $assignedCampaign = array_values($assignedCampaign);
            return (empty($assignedCampaign)) ? array(
                'success' => true,
                'data' => array(),
                    ) : array(
                'success' => false,
                'data' => $assignedCampaign,
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


    private function discountTypeStringify($discount_type)
    {
        $returnString = null;
        switch ($discount_type)
        {
            case "shipping_total":
                $returnString = "Shipping";
                break;
            case "order_total":
                $returnString = "Order";
                break;

            default:
                $returnString = "Product";
        }

        return $returnString;
    }
}
