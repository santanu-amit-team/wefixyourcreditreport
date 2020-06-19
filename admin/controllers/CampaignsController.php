<?php

namespace Admin\Controller;

use Exception;
use Lazer\Classes\Database;
use Lazer\Classes\Helpers\Validate;
use Lazer\Classes\LazerException;
use Application\Request;
use Application\Config;
use Symfony\Component\PropertyAccess\PropertyAccess;

class CampaignsController
{

    private $table;

    public function __construct()
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
        $this->productTypes = array(
            '1' => 'Regular',
            '2' => 'Prepaid',
            '3' => 'Scrap',
        );
            
        $this->table = array(
            'name' => 'campaigns',
            'attr' => array(
                'id' => 'integer',
                'campaign_label' => 'string',
                'crm_id'    => 'integer',
                'campaign_type' => 'integer',
                'campaign_id' => 'string',
                'coupon_ids' => 'string',
                'shipping_id' => 'string',
                'shipping_price' => 'string',
                'product_id' => 'string',
                'product_price' => 'string',
                'product_key' => 'string',
                'product_quantity' => 'integer',
                'rebill_product_id' => 'string',
                'rebill_product_price' => 'string',
                'prepaid_campaign_id' => 'integer',
                'scrap_campaign_id' => 'integer',
                'last_modified' => 'string',
                'product_active_on_list' => 'boolean',
                'product_description' => 'string',
                'product_paths' => 'string',
                'product_type' => 'integer',
                'product_sku' => 'string',
                'product_schedule' => 'string',
                'product_schedule_quantity' => 'integer',
                'enable_billing_module' => 'boolean',
                'offer_id' => 'string',
                'billing_model_id' => 'string',
                'trial_product_id' => 'string',
                'trial_product_price' => 'string',
                'trial_product_quantity' => 'string',
                'children_settings' => 'string',
                'trial_children_settings' => 'string',
                'nmi_plan_id' => 'string',
                'billing_type' => 'integer',
                'connection_id' => 'integer',
                'product_array' => 'string',
                'shipping_profiles' => 'string',
                'enable_prepaid_campaigns' => 'boolean',
                'enable_order_filter_campaigns' => 'boolean',
                "enable_product_shipping_auto_sync" => 'boolean',
                "enable_campaign_limit" => 'boolean',
                "campaign_limit" => 'string',
                "alter_campaignid" => 'string'
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

            if (!empty($campaignType))
            {
                $query = Database::table($this->table['name'])
                        ->where('campaign_type', '=', $campaignType)
                        ->orderBy($orderByField, $orderBy);
            }
            else
            {
                $query = Database::table($this->table['name'])
                        ->orderBy($orderByField, $orderBy);
            }

            // $query->with('crms');

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
                    // $couponIDS = (!empty($data[$key]['coupon_ids'])) ? json_decode($data[$key]['coupon_ids'], true) : '';
                    // $data[$key]['coupon_ids'] = $couponIDS;


                    // $last_modified_formated = isset($dataValue['last_modified']) ? date('l d F Y h:i A', strtotime($dataValue['last_modified'])) : '';
                    $last_modified_formated = isset($dataValue['last_modified']) ? date('M j, Y', strtotime($dataValue['last_modified'])) : '';
                    $data[$key]['last_modified_formated'] = $last_modified_formated;
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

    public function listAll($campaignType = '')
    {
        try
        {
            if (!empty($campaignType))
            {
                $rows = Database::table($this->table['name'])
                                ->where('campaign_type', '=', $campaignType)->findAll();
            }
            else
            {
                $rows = Database::table($this->table['name'])->findAll();
            }
            $data = array();
            foreach ($rows as $row)
            {
                array_push($data, array(
                    'id' => $row->id,
                    'campaign_label' => $row->campaign_label,
                    'campaign_type' => $row->campaign_type,
                ));
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
            if ($updateStatus)
            {
                $data['last_modified'] = $row->last_modified = date("Y-m-d H:i:s");
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

    
    private function getUsedCampaignCount($value , $data_count)
    {
        $allConfig = Database::table('configurations')
                ->orderBy('id', 'desc')
                ->findAll()->asArray();
        
        if(!empty($allConfig))
        {
            foreach ($allConfig as $c)
            {
                $camp_arr = !empty($c['campaign_ids']) ? json_decode($c['campaign_ids']) : array();
                if(in_array($value, $camp_arr))
                {
                    $data_count = $data_count + 1;
                    break;
                }
            }
            
            return $data_count;
        }
    }


    public function delete($id='')
    {
        try
        {
            $selectedIds = ($id == '') ? Request::get('ids') : array($id);
            $deletedIds = $notDeletedIds = [];
            $data_count = 0;
            foreach ($selectedIds as $key => $value) {
                $data_count = 0;
                //$data_count = Database::table('configurations')->where('crm_id', '=', (int)$value)->find()->count();
                
                $data_count = $this->getUsedCampaignCount($value, $data_count);
                
                if($data_count != 0){
                    break;
                }   
            }
            if ($data_count > 0)
            {
                return array(
                    'success' => false,
                    'data' => array(),
                    'error_message' => ($id == '') ? 'Some of selected Campaign is already used in Configuration': 'Sorry! This Campaign is already used in Configuration',
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
        if (empty($data->campaign_label))
        {
            throw new Exception("Campaign label is required");
        }
        if (
                empty($data->campaign_type) ||
                !in_array(
                        $data->campaign_type, array_keys($this->productTypes)
                )
        )
        {
            throw new Exception("Product type is required");
        }
        return true;
    }

    public function checkProductManagementExtension()
    {
        $isProductManagementActive = false;
        $extensions = Config::extensions();
        $enableImportExport = Config::extensionsConfig('ProductManagement.enable_import_export');
        foreach ($extensions as $extension)
        {
            if (
                    $extension['extension_slug'] === 'ProductManagement' &&
                    $extension['active'] === true
            )
            {
                $isProductManagementActive = true;
                break;
            }
        }
        return array(
            'success' => true,
            'isProductManagementActive' => $isProductManagementActive,
            'enableImportExport' => $enableImportExport
        );
    }

    public function checkExtensions($extentionName = '')
    {
       
        $extentionName = strlen($extentionName) ? $extentionName : Request::get('extention');
       
        $result = array(
            'success' => true,
            'extensionCouponsActive' => false,
        );
        $extensions = Config::extensions();

        foreach ($extensions as $extension)
        {
            if ($extension['extension_slug'] !== $extentionName)
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
    
    public function getCrmCampaignData() {
        $crmData = Config::crms(sprintf('%d', Request::form()->get('crm_id')));
        $crmClass = sprintf('Application\Model\%s', ucfirst($crmData['crm_type']));
        return call_user_func_array(
                array($crmClass, 'getCrmCampaignData'), array($crmData)
        );
    }
}
