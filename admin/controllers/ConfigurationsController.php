<?php

namespace Admin\Controller;

use Application\Config;
use Application\Request;
use Exception;
use Lazer\Classes\Database;
use Lazer\Classes\Helpers\Validate;
use Lazer\Classes\LazerException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Admin\Controller\ExtensionsController;

class ConfigurationsController
{

    private $table;

    public function __construct()
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
        $this->table    = array(
            'name' => 'configurations',
            //OLD
            // 'attr' => array(
            //     "id"                         => "integer",
            //     "configuration_label"        => "string",
            //     "campaign_ids"               => "string",
            //     "crm_id"                     => "integer",
            //     "upsell_preferred_method"    => "string",
            //     "accept_prepaid_cards"       => "boolean",
            //     "enable_downsells"           => "boolean",
            //     "site_title"                 => "string",
            //     "meta_description"           => "string",
            //     "force_gateway_id"           => "string",
            //     "preserve_gateway"           => "boolean",
            //     "enable_delay"               => "boolean",
            //     "delay_time"                 => "integer",
            //     "split_charge"               => "boolean",
            //     "split_campaign_ids"         => "string",
            //     "link_with_parent"           => "boolean",
            //     "order_placement_method"     => "string",
            //     "split_enable_delay"         => "boolean",
            //     "split_force_parent_gateway" => "boolean",
            //     "split_delay_time"           => "integer",
            //     "exit_popup_enabled"         => "boolean",
            //     "exit_popup_element_id"      => "string",
            //     "exit_popup_page"            => "string",
            //     "notes"                      => "string",
            //     "split_preferred_method"     => "string",
            //     "enable_kount"               => "boolean",
            //     "kount_pixel"                => "string",
            //     "initialize_new_subscription"  => "boolean",
            //     "split_initialize_new_subscription"  => "boolean",
            //     "additional_crm" => "boolean",
            //     "additional_crm_id" => "string",
            //     "additional_crm_test_card" => "string",
            //     "disable_test_flow" => "boolean",
            //     "disable_prospect_flow" => "boolean",
            //     "force_parent_gateway" => "boolean",
            //     "enable_dynamic_delay"  => "boolean",
            //     "dynamic_delay" => "string",
            //     "enable_split_dynamic_delay" => "boolean",
            //     "split_dynamic_delay" => "string",
            //     "authorization_amounts" => "string"
            // ),
            'attr' => array(
                "id"                             => "integer",
                "configuration_label"            => "string",
                "crm_id"                         => "integer",
                "campaign_ids"                   => "string",
                "step"                           => "string", //new
                "upsell_preferred_method"        => "string",
                "accept_prepaid_cards"           => "boolean",
                "process_fraud_declines"         => "boolean", //new
                "enable_decline_reprocessing"    => "boolean", //new
                "fraud_decline_campaign"         => "integer", //new
                "decline_reprocessing_campaign"  => "integer", //new
                "crm_gateway_settings"           => "string", //new
                "force_gateway_id"               => "string",
                "mid_routing_profile"            => "string", //new
                "enable_preauth"                 => "boolean", //new
                "preauth_amount"                 => "string", //new
                "enable_preauth_retry"           => "boolean", //new
                "retry_preauth_amount"           => "string", //new
                "enable_delay"                   => "boolean",
                "delay_type"                     => "string", //new
                "delay_time"                     => "integer",
                "dynamic_delay"                  => "string",
                "purge_time"                     => "string", //new
                "additional_crm"                 => "boolean",
                "disable_test_flow"              => "boolean",
                "disable_prospect_flow"          => "boolean",
                "force_parent_gateway"           => "boolean",
                "additional_crm_type"            => "string", //new
                "additional_crm_id"              => "string",
                "additional_crm_test_card"       => "string",
                "order_using_test_card"          => "boolean", //new
                "enable_post_site_url"           => "boolean", //new
                "url_source"                     => "string", //new
                "site_url"                       => "string", //new
                "site_title"                     => "string",
                "meta_description"               => "string",
                "split_charge"                   => "boolean",
                "split_preferred_method"         => "string",
                "split_crm_id"                   => "integer", //new
                "split_campaign_ids"             => "string",
                "link_with_parent"               => "boolean",
                "split_force_parent_gateway"     => "boolean",
                "split_enable_delay"             => "boolean",
                "split_delay_type"               => "string", //new
                "split_delay_time"               => "integer",
                "split_dynamic_delay"            => "string",
                "split_purge_time"               => "string", //new
                "enable_downsells"               => "boolean",
                "exit_popup_enabled"             => "boolean",
                "exit_popup_element_id"          => "string",
                "exit_popup_page"                => "string",
                "preserve_gateway"               => "boolean",
                "initialize_new_subscription"    => "boolean",
                "split_initialize_new_subscription"  => "boolean",
                "enable_website_post" => "boolean", //new
                "website_id" => "string", //new
                "enable_split_preauth"                 => "boolean", //new
                "preauth_split_amount"                 => "string", //new
                "enable_preauth_retry_split"           => "boolean", //new
                "retry_preauth_amount_split"           => "string", //new
                "remote_lbp"                           => "boolean",
                "data_capture"                         => "boolean",
                "decline_reasons"                      => "string",
                "enable_pixel_fire"                    => "boolean",
                "enable_affiliate_override"            => "boolean",
                "decline_affiliate_override"           => "string",
                "force_campaign_to_upsell"             => "boolean"
            )
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
            $data = Database::table($this->table['name'])
                ->orderBy('id', 'desc')
                ->findAll()->asArray();

            if (!empty($data)) {
                foreach ($data as $key => $val) {
                    $array_of_name = [];
                    $crm_val       = '';
                    $campaign_ids  = isset($val['campaign_ids']) ? json_decode($val['campaign_ids'], true) : '';
                    if (!empty($campaign_ids)) {
                        foreach ($campaign_ids as $campaign_ids_val) {
                            $campaign_info = Database::table('campaigns')->where('id', '=', $campaign_ids_val)->where('campaign_type', '=', 1)->find();
                            if (isset($campaign_info->id)) {
                                $array_of_name[] = $campaign_info->campaign_label . " (" . $campaign_info->id . ")";
                            }
                        }
                    }
                    $data[$key]['campaign_name'] = !empty($array_of_name) ? implode(", ", $array_of_name) : '';

                    if (!empty($val['crm_id'])) {
                        $crm_info = Database::table('crms')->where('id', '=', $val['crm_id'])->find();
                        $crm_val  = isset($crm_info->id) ? $crm_info->crm_label . " (" . $crm_info->id . ")" : '';
                    }

                    $data[$key]['crm_name'] = ($crm_val ? $crm_val : '');
                    $data[$key]['crm_type'] = ($crm_val ? $crm_info->crm_type : '');
                }
            }

            $extensionList = $this->getExtensionList();
            $slugs = [];
            if($extensionList['success']) {
                $extensionList = $extensionList['data'];
                foreach($extensionList as $extension) {
                    array_push($slugs, $extension['extension_slug']);
                }

                if(in_array('Limelight3DS', $slugs)) {
                
                }
                $exClass = new ExtensionsController("");
                foreach($data as $key => $each) {

                    if($exClass->isExtensionActiveInConfig($each['id'], 'Limelight3DS')['success']) {
                       $data[$key]['is3ds'] = true;
                    }
                    else {
                       $data[$key]['is3ds'] = false;
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
                /* campaigns */
                // if ($key == 'campaign_ids' || $key == 'split_campaign_ids') {
                //     $valueGet = $this->campaignCheck($valueGet);
                // }
                $data[$key] = ($valueGet !== null) ? $valueGet : '';
            }

            /* crms */
            $crm_info         = Database::table('crms')->where('id', '=', $data['crm_id'])->find();
            $data['crm_type'] = isset($crm_info->crm_type) ? $crm_info->crm_type : '';
            $data['crm_id']   = isset($crm_info->id) ? $crm_info->id : '';

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
                $data[$key] = $row->{$key} = $this->filterInput($key);
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
                $data[$key] = $row->{$key} = $this->filterInput($key);
            }
            if ($this->isValidData($row)) {
                $row->save();
                $data['id'] = $id;
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

    /**
     * Multiple delete
     *
     */
    public function delete($id='')
    {
        try
        {
            $selectedIds = ($id == '') ? Request::get('ids') : array($id);
            $deletedIds = $notDeletedIds = [];
            /* In rotators check */
            $data        = Database::table('rotators')->findAll()->asArray();
            $rotator_ids = [];
            if (!empty($data)) {
                foreach ($data as $data_val) {
                    if (!empty($data_val['configuration_ids'])) {
                        $rotator_ids = json_decode(stripslashes($data_val['configuration_ids']), true);
                    }
                }
            }

            if (!empty($rotator_ids)) {
                foreach ($selectedIds as $selectedId) {
                    if (in_array($selectedId, $rotator_ids)) {
                        return array(
                            'success'       => false,
                            'data'          => array(),
                            'error_message' => 'Sorry! This Configuration is already used in Rotators',
                        );
                    }
                }
            }

            /* In Affiliates check */
            $data          = Database::table('affiliates')->findAll()->asArray();
            $affiliate_ids = [];
            if (!empty($data)) {
                foreach ($data as $data_val) {
                    if (!empty($data_val['configuration_ids'])) {
                        $affiliate_ids = json_decode(stripslashes($data_val['configuration_ids']), true);
                    }
                }
            }

            if (!empty($affiliate_ids)) {
                foreach ($selectedIds as $selectedId) {
                    if (in_array($selectedId, $affiliate_ids)) {
                        return array(
                            'success'       => false,
                            'data'          => array(),
                            'error_message' => 'Sorry! This Configuration is already used in Affiliates',
                        );
                    }
                }
            }

            /* In Pixels check */
            $data_count = Database::table('pixels')->where('configuration_id', 'IN', $selectedIds)->find()->count();


            if ($data_count) {
                return array(
                    'success'       => false,
                    'data'          => array(),
                    'error_message' => 'Sorry! This Configuration is already used in Pixel',
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
                'data'    => array(),
                'deleted_ids' => $deletedIds,
                'not_deleted_ids' => $notDeletedIds 
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

    private function campaignCheck($valueGet)
    {

        $valueGet = json_decode($valueGet, 1);
        if (!empty($valueGet)) {
            $campaignIds   = [];
            $campaignInfos = Database::table('campaigns');
            $i_val         = 0;
            foreach ($valueGet as $valueGetPer) {

                $campaignInfos = (($i_val == 0) ? $campaignInfos->where('id', '=', $valueGetPer) : $campaignInfos->orWhere('id', '=', $valueGetPer));
                $i_val++;
            }
            $campaignInfos = $campaignInfos->findAll();
            if (!empty($campaignInfos)) {
                foreach ($campaignInfos as $campaignInfo) {
                    $campaignIds[] = $campaignInfo->id;
                }
            }
            return $campaignIds;
        }
        return [];
    }

    public function checkAsyncSplitExtension()
    {
        $isAsyncSplitActive = false;
        $extensions         = Config::extensions();
        foreach ($extensions as $extension) {
            if (
                $extension['extension_slug'] === 'AsyncSplit' &&
                $extension['active'] === true
            ) {
                $isAsyncSplitActive = true;
                break;
            }
        }
        return array(
            'success'            => true,
            'isAsyncSplitActive' => $isAsyncSplitActive,
        );
    }
        
    public function getExtensionList() {

        try { 
            $extensions = Config::extensions();
            $data = array();
            foreach($extensions as $ext) {

                $isListable = ExtensionsController::getAttr($ext['extension_slug'], 'listInFunnelConfig');
                if($isListable) {
                    array_push($data, array(
                        'extension_id' => $ext['id'],
                        'extension_name' => $ext['extension_name'],
                        'extension_slug' => $ext['extension_slug'],
                    ));
                }
            }
            
            if(!empty($data)) {
                return array(
                    'success'       => true,
                    'data'          => $data
                );
            }

            return array(
                'success'       => false,
                'data'          => null,
                'error_message' => 'No extension found to be listed.'
            );
        }
        catch(\Exception $e) {
            return array(
                'success'       => false,
                'data'          => null,
                'error_message' => $e->getMessage()
            );
        }
    }

}
