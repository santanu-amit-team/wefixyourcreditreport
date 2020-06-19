<?php

namespace Admin\Controller;

use Application\Config;
use Application\Request;
use Exception;
use Lazer\Classes\Database;
use Lazer\Classes\Helpers\Validate;
use Lazer\Classes\LazerException;
use Symfony\Component\PropertyAccess\PropertyAccess;

class AffiliatesController
{

    private $table;
    private $affiliate_key;

    public function __construct()
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
        $this->storagePath = STORAGE_DIR . DS;
        $this->table    = array(
            'name' => 'affiliates',
            'attr' => array(
                'id'                    => 'integer',
                'label'                 => 'string',
                'affid'                 => 'string',
                'afid'                  => 'string',
                'sid'                   => 'string',
                'c1'                    => 'string',
                'c2'                    => 'string',
                'c3'                    => 'string',
                'c4'                    => 'string',
                'c5'                    => 'string',
                'aid'                   => 'string',
                'opt'                   => 'string',
                'click_id'              => 'string',
                'config_type'           => 'string',
                'configuration_mapping' => 'string',
                'campaign_mapping'      => 'string',
                'scrap_step_1'          => 'string',
                'scrap_step_2'          => 'string',
                "enable_funnel_configuration" => "boolean",
                'enable_campaign_configuration' => 'boolean',
                'enable_aff_network' => 'boolean',
                'nid' => 'string'
            ),
        );

        $this->affiliate_key = array(
            'affid',
            'afid',
            'sid',
            'c1',
            'c2',
            'c3',
            'c4',
            'c5',
            'aid',
            'opt',
            'click_id',
        );

        $this->takeOneKey = array(
            'affid',
            'afid',
            'aid',
        );

        try
        {
            Validate::table($this->table['name'])->exists();
        } catch (LazerException $ex) {
            Database::create(
                $this->table['name'], $this->table['attr']
            );
        }
    }

    public function all()
    {
        try
        {
            $data = Database::table($this->table['name'])->orderBy('id', 'desc')->findAll()->asArray();
            if (!empty($data)) {
                foreach ($data as $key => $val) {
                    $array_of_name     = [];
                    $query_array       = [];
                    $new_query_array   = [];
                    $configuration_ids = isset($val['configuration_ids']) ? json_decode($val['configuration_ids'], true) : '';
                    if (!empty($configuration_ids)) {
                        foreach ($configuration_ids as $configuration_ids_val) {
                            $configuration_info = Database::table('configurations')->where('id', '=', $configuration_ids_val)->find();
                            if (isset($configuration_info->id)) {
                                $array_of_name[] = (!empty($configuration_info->configuration_label) ? $configuration_info->configuration_label : 'N/A') . " (" . $configuration_info->id . ")";
                            }
                        }
                    }
                    $data[$key]['configurations'] = !empty($array_of_name) ? implode(", ", $array_of_name) : 'N/A';

                    $skipKeys = false;
                    foreach ($this->affiliate_key as $value) {
                        if (isset($data[$key][$value])) {
                            $query_array[$value] = $data[$key][$value];
                        } else {
                            $query_array[$value] = '';
                        }
                    }
                    
                    $network_ids = isset($val['nid']) ? $val['nid'] : '';
                    
                    if(!empty($network_ids)) {
                       $new_query_array['nid'] = $network_ids;
                    }

                    $new_query_array_new = $this->updateQueryArray($query_array);
                    
                    $new_query_array_new = array_merge($new_query_array, $new_query_array_new);
                    
                    $data[$key]['affiliate_url'] = Request::getOfferUrl() . '?' . http_build_query($new_query_array_new);
                    
                    try {
                        $file = $this->storagePath . 'network.json';
                        $fileData = file_get_contents($file);

                        if(!empty($fileData) && !empty($network_ids)) {
                            $fileDataArray = json_decode($fileData, true);
                            foreach ($fileDataArray as $k => $v) {
                                if($v['network_value'] == $network_ids) {
                                    $networkName[$key] = $v['network_name'];
                                    break;
                                }
                            }
                        }
                    } catch (Exception $ex) {
                        
                    }
                   
                    if(!empty($networkName[$key])) {
                        $data[$key]['network_name'] = $networkName[$key];
                    } else {
                        $data[$key]['network_name'] = 'N/A';
                    }
                }
                
            }

            return array(
                'success' => true,
                'data'    => $data,
            );
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
            $row  = Database::table($this->table['name'])->where('id', '=', $id)->find()->asArray();
            $data = array();
            if (empty($row)) {
                return array(
                    'success' => false,
                    'data'    => array(),
                );
            }
            foreach ($this->table['attr'] as $key => $type) {
                $valueGet = $this->accessor->getValue($row[0], '[' . $key . ']');
                /* configurations */
                if ($key == 'configuration_mapping') {
                    $valueGet = $this->configurationCheck($valueGet);
                }
                /* campaigns */
                if ($key === 'campaign_mapping') {
                    $valueGet = $this->campaignCheck($valueGet);
                }
                $data[$key] = ($valueGet !== null) ? $valueGet : '';
            }

            return array(
                'success' => true,
                'data'    => $data,
            );
        } catch (Exception $ex) {
            return array(
                'success'       => false,
                'data'          => array(),
                'error_message' => $ex->getMessage(),
            );
        }
    }

    public function add()
    {
        try
        {
            $row  = Database::table($this->table['name']);
            $data = array();
            foreach ($this->table['attr'] as $key => $type) {
                if ($key === 'id') {
                    continue;
                }
                $valueGet = $this->filterInput($key);
                if ($key === 'configuration_mapping') {
                    $valueGet = $this->filterConfigMapping($this->filterInput($key));
                }
                if ($key === 'campaign_mapping') {
                    $valueGet = $this->filterConfigMapping($this->filterInput($key));
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

    public function edit($id = '')
    {
        try
        {
            $row  = Database::table($this->table['name'])->find($id);
            $data = array();
            foreach ($this->table['attr'] as $key => $type) {
                if ($key === 'id') {
                    continue;
                }
                $valueGet = $this->filterInput($key);
                if ($key === 'configuration_mapping') {
                    $valueGet = $this->filterConfigMapping($this->filterInput($key));
                }
                if ($key === 'campaign_mapping') {
                    $valueGet = $this->filterConfigMapping($this->filterInput($key));
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
                'data'          => array(),
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
        return true;
    }

    private function configurationCheck($valueGet)
    {
        $valueGet = json_decode($valueGet, 1);
        if (!empty($valueGet)) {
            $configurationMain = [];
            foreach ($valueGet as $valueGetPer) {
                $configurationIds = [];
                foreach ($valueGetPer as $value) {
                    if (!empty($value)) {
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
    
    private function campaignCheck($valueGet)
    {
        $valueGet = json_decode($valueGet, 1);
        if (!empty($valueGet)) {
            $campaignMain = [];
            foreach ($valueGet as $valueGetPer) {
                $campaignIds = [];
                foreach ($valueGetPer as $value) {
                    if (!empty($value)) {
                        $campaignInfos = Database::table('campaigns')->find($value);
                        $campaignIds[] = isset($campaignInfos->id) ? $campaignInfos->id : '';
                    }
                }
                $campaignMain[] = $campaignIds;
            }
            return $campaignMain;
        }
        return [];
    }

    private function filterConfigMapping($valueGet)
    {
        $valueGet2 = json_decode($valueGet, 1);
        $newValue  = [];
        if (!empty($valueGet2)) {
            foreach ($valueGet2 as $valueGet2val) {
                if (!empty($valueGet2val[0]) && !empty($valueGet2val[1])) {
                    $newValue[] = array($valueGet2val[0], $valueGet2val[1]);
                }
            }
            $valueGet = !empty($newValue) ? json_encode($newValue) : "";
        } else {
            $valueGet = "";
        }

        return $valueGet;
    }

    private function updateQueryArray($query_array)
    {
        $keyTake     = '';
        $arr         = $this->takeOneKey;
        $removeArray = [];
        foreach ($arr as $keyValue) {
            if ($query_array[$keyValue] != '') {
                $keyTake = $keyValue;
                break;
            }
        }
        $removeArray = !empty($keyTake) ? array_diff($arr, array($keyTake)) : array_diff($arr, array('affid'));
        foreach ($removeArray as $removeArrayValue) {
            unset($query_array[$removeArrayValue]);
        }
        return $query_array;
    }

    public function checkExtensions()
    {
        $result = array(
            'success'                            => true,
            'extensionAffiliatesActive'          => false
        );

        $extensions = Config::extensions();
        foreach ($extensions as $extension) {
            if ($extension['extension_slug'] !== 'Affiliates') {
                continue;
            }
            if ($extension['active'] === true) {
                $result['extensionAffiliatesActive'] = true;
            }
            break;
        }

        return $result;

    }
    
    public function saveNetworkDetails()
    {
        try {
           $formData = Request::form()->all();
            $file = $this->storagePath . 'network.json';
            $fileData = file_get_contents($file);

            if(empty($fileData)) {
                file_put_contents($file, json_encode(array($formData), true));
            } else {
                $decodedData = json_decode($fileData);
                $formDataArray[0] = $formData;
                $finalArray = array_merge($formDataArray, $decodedData);
                file_put_contents($file, json_encode($finalArray, true));
            } 
            
            return array(
                'success' => true,
                'data' => array()
            );
        } catch (Exception $ex) {
            return array(
                'success' => false,
                'data' => array(),
                'error_message' => $ex->getMessage(),
            );
        }
    }
    
    public function getNetworkDetails()
    {
        try {
            $file = $this->storagePath . 'network.json';
            $fileData = file_get_contents($file);

            if(empty($fileData)) {
                return array(
                    'success' => true,
                    'data' => array()
                );
            } else {
                $decodedData = json_decode($fileData);
                return array(
                    'success' => true,
                    'data' => $decodedData
                );
            } 
            
            
        } catch (Exception $ex) {
            return array(
                'success' => false,
                'data' => array(),
                'error_message' => $ex->getMessage(),
            );
        }
    }

}
