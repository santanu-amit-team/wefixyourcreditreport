<?php

namespace Admin\Controller;

use Application\Config;
use Application\Request;
use Exception;
use Lazer\Classes\Database;
use Lazer\Classes\Helpers\Validate;
use Lazer\Classes\LazerException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Database\Connectors\ConnectionFactory;

class UpsellManagerController
{

    private $table;
    private $affiliate_key;

    public function __construct()
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
        $this->table = array(
            'name' => 'upsellmanager',
            'attr' => array(
                'id' => 'integer',
                'label' => 'string',
                'deviceType' => 'string',
                'upsellData' => 'string',
                "status" => "string"
            //  'last_edited' => 'string'
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
        $this->upsellManagerPath = sprintf('%s%supsellmanager', dirname(dirname(dirname(__FILE__))), DS);
    }

    public function all()
    {
        try
        {
            $data = Database::table($this->table['name'])->orderBy('id', 'desc')->findAll()->asArray();
            if (!empty($data))
            {
                foreach ($data as $key => $val)
                {
                    $array_of_name = [];
                    $query_array = [];
                    $new_query_array = [];
                    $configuration_ids = isset($val['configuration_ids']) ? json_decode($val['configuration_ids'], true) : '';
                    if (!empty($configuration_ids))
                    {
                        foreach ($configuration_ids as $configuration_ids_val)
                        {
                            $configuration_info = Database::table('configurations')->where('id', '=', $configuration_ids_val)->find();
                            if (isset($configuration_info->id))
                            {
                                $array_of_name[] = (!empty($configuration_info->configuration_label) ? $configuration_info->configuration_label : 'N/A') . " (" . $configuration_info->id . ")";
                            }
                        }
                    }
                    $data[$key]['configurations'] = !empty($array_of_name) ? implode(", ", $array_of_name) : 'N/A';

                    $skipKeys = false;
                    foreach ($this->affiliate_key as $value)
                    {
                        if (isset($data[$key][$value]))
                        {
                            $query_array[$value] = $data[$key][$value];
                        }
                        else
                        {
                            $query_array[$value] = '';
                        }
                    }

                    $new_query_array = $this->updateQueryArray($query_array);

                    $data[$key]['affiliate_url'] = Request::getOfferUrl() . '?' . http_build_query($new_query_array);
                }
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
                /* configurations */
                if ($key == 'configuration_mapping')
                {
                    $valueGet = $this->configurationCheck($valueGet);
                }
                $data[$key] = ($valueGet !== null) ? $valueGet : '';
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
            
            $copy = Request::form()->get('copy');
            Request::form()->remove('copy');
//            print_r(Request::form()->all());die;
            $row = Database::table($this->table['name']);
            $data = array();
            foreach ($this->table['attr'] as $key => $type)
            {
                if ($key === 'id')
                {
                    continue;
                }
                $valueGet = $this->filterInput($key);

                $data[$key] = $row->{$key} = $valueGet;
            }
            if(!empty($copy)){
                $row->status = "inactive";
            }
            if ($this->isValidData($row))
            {
                $row->save();
                $this->resetDetails();
            if(empty($copy)){
                Request::form()->set('id',$row->id);
                $this->changeStatus();
            }
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
                $valueGet = $this->filterInput($key);
                if ($key === 'configuration_mapping')
                {
                    $valueGet = $this->filterConfigMapping($this->filterInput($key));
                }
                $data[$key] = $row->{$key} = $valueGet;
            }
            if ($this->isValidData($row))
            {
                $row->save();
                $this->resetDetails();
                Request::form()->set('id',$row->id);
                $this->changeStatus();
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
                'data' => array(),
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

            foreach ($selectedIds as $key => $selectedId)
            {
                $res = Database::table($this->table['name'])->find($selectedId)->delete();

                if ($res)
                {
                    $deletedIds[] = $selectedId;
                }
                else
                {
                    $notDeletedIds[] = $selectedId;
                }
            }

            return array(
                'success' => true,
                'data' => array(),
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

    private function isValidData($data)
    {
        return true;
    }

    private function configurationCheck($valueGet)
    {
        $valueGet = json_decode($valueGet, 1);
        if (!empty($valueGet))
        {
            $configurationMain = [];
            foreach ($valueGet as $valueGetPer)
            {
                $configurationIds = [];
                foreach ($valueGetPer as $value)
                {
                    if (!empty($value))
                    {
                        $configurationInfos = Database::table('configurations')->find($value);
                        $configurationIds[] = isset($configurationInfos->id) ? $configurationInfos->id : '';
                    }
                }
                $configurationMain[] = $configurationIds;
            }
            return $configurationMain;
        }
        return [];
    }

    private function filterConfigMapping($valueGet)
    {
        $valueGet2 = json_decode($valueGet, 1);
        $newValue = [];
        if (!empty($valueGet2))
        {
            foreach ($valueGet2 as $valueGet2val)
            {
                if (!empty($valueGet2val[0]) && !empty($valueGet2val[1]))
                {
                    $newValue[] = array($valueGet2val[0], $valueGet2val[1]);
                }
            }
            $valueGet = !empty($newValue) ? json_encode($newValue) : "";
        }
        else
        {
            $valueGet = "";
        }

        return $valueGet;
    }

    private function updateQueryArray($query_array)
    {
        $keyTake = '';
        $arr = $this->takeOneKey;
        $removeArray = [];
        foreach ($arr as $keyValue)
        {
            if ($query_array[$keyValue] != '')
            {
                $keyTake = $keyValue;
                break;
            }
        }
        $removeArray = !empty($keyTake) ? array_diff($arr, array($keyTake)) : array_diff($arr, array('affid'));
        foreach ($removeArray as $removeArrayValue)
        {
            unset($query_array[$removeArrayValue]);
        }
        return $query_array;
    }

    public function checkExtensions()
    {
        $result = array(
            'success' => true,
            'extensionAffiliatesActive' => false
        );

        $extensions = Config::extensions();
        foreach ($extensions as $extension)
        {
            if ($extension['extension_slug'] !== 'UpsellManager')
            {
                continue;
            }
            if ($extension['active'] === true)
            {
                $result['extensionUpsellManagerActive'] = true;
            }
            break;
        }

        return $result;
    }

    public function getCampaigns()
    {
        $campaigns = array();
        foreach (Config::campaigns() as $value)
        {
            if ($value['campaign_type'] == 1)
                array_push($campaigns, $value);
        }
        return $campaigns;
    }

    public function getConfig()
    {
        return Config::configurations();
    }

    public function changeStatus()
    {
        try
        {
            $desiredUpsellDetails = array();
            $id = Request::form()->get('id');
            $rows = Database::table($this->table['name'])->findAll()->asArray();
            foreach ($rows as $key => $row)
            {
                // print_r($row);
                //echo $row->id." ".$id;

                $snglRow = Database::table($this->table['name'])->find($row['id']);
                if ($row['id'] == $id)
                {
                    $desiredUpsellDetails = $row;
                    $snglRow->status = Request::form()->get('status');
                }
                else if ($row['deviceType'] == Request::form()->get('deviceType'))
                {
                    $snglRow->status = "inactive";
                }

                $snglRow->save();
                // $row->set(array(
                // 'nickname' => 'user',
                // 'email' => 'user@example.com'
                // ));
                // }
            }
            $this->resetDetails();
            // die;
            //$data = (object)$row[0];
            //$data->save();
            // echo "<pre>"; print_r($rows);die;
            // $row = Database::table($this->table['name'])->find($id);
            // $row->status = Request::form()->get('status');
            // $row->save();
            // $this->makeUpsellController($desiredUpsellDetails);
            return array(
                'success' => true,
                'data' => array(
                ),
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

   

    public function resetDetails(){
        $upsellManageVariationPath = STORAGE_DIR . DS . 'upsellvariation.json';
        $upsellManageVariationMobilePath = STORAGE_DIR . DS . 'upsellvariationMobile.json';
        file_put_contents($upsellManageVariationPath, "",LOCK_EX);
        file_put_contents($upsellManageVariationMobilePath, "",LOCK_EX);
    }
    
    public function getTemplates() {
        $id = Request::form()->get('id');
        $DbCnc = $this->getTemplateDbCnnc();
        $data = $DbCnc->table('templates_container')
                        ->select('*')->where('status', 'active');
        if(empty($data)){
            return false;
        }
        $result = array();$sngl = array();$multi = array();
        foreach (array_values($data->get()) as $key => $value) {
            if(!empty($id) && $id == $value['id']){
                return $value;
            }
            if($value['type'] == "single_product"){
                array_push($sngl, $value);
            }else{
                array_push($multi, $value);
            }
           
        }
        $result['single_product'] = $sngl;
        $result['multi_product'] = $multi;
        return $result;
    }
    
    private function getTemplateDbCnnc()
    {
        $factory = new ConnectionFactory();
        return $factory->make(array(
                    'driver' => 'sqlite',
                    'database' => STORAGE_DIR . DS . 'prebuilt_templates',
        ));
    }

}
