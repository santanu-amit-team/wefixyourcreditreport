<?php

namespace Admin\Controller;

use Exception;
use Lazer\Classes\Database;
use Lazer\Classes\Helpers\Validate;
use Lazer\Classes\LazerException;
use Application\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;

class PixelsController
{

	private $table;

	public function __construct()
	{
		$this->accessor = PropertyAccess::createPropertyAccessor();
		$this->table = array(
			'name' => 'pixels',
			'attr' => array(
				// old
				// "id" => "integer",
				// "pixel_name" => "string",
				// "pixel_type" => "string",
				// "postback_url" => "string",
				// "click_pixel" => "string",
				// "convert_pixel" => "string",
				// "prepaid" => "boolean",
				// "affiliate_id" => "string",
				// "sub_id" => "string",
				// "page" => "string",
				// "device" => "string",
				// "os" => "string",
				// "pixel_placement" => "string",
				// "html_pixel" => "string",
				// "third_party_postback_url" => "string",
				// "third_party_html" => "string",
				// "configuration_id" => "integer",
    //             "multi_fire" => "boolean"
				"id" => "integer",
				"pixel_name" => "string",
				"pixel_type" => "string",
				"ignore_pixel_on_order_filter" => "boolean",
				"postback_url" => "string",
				"click_pixel" => "string",
				"convert_pixel" => "string",
				"prepaid" => "boolean",
				"enable_affiliate_parameters" => "boolean",
				"affiliate_id_key" => "string",
				"affiliate_id_value" => "string",
				"sub_id_key" => "string",
				"sub_id_value" => "string",
				"page" => "string",
				"device" => "string",
				"os" => "string",
				"pixel_placement" => "string",
				"html_pixel" => "string",
				"third_party_postback_url" => "string",
				"third_party_html" => "string",
				"configuration_id" => "string",
                "multi_fire" => "boolean",
                "pixel_firing_priority" => "string",
                "pixel_firing_option" => "string",
                "fire_live_transactions" => "boolean",
                "enable_custom_firing_schedule" => "boolean",
                "start_date" => "string",
                "end_date" => "string",
                "start_time" => "string",
                "end_time" => "string",
                "time_zone" => "string",
                "enable_page" => "boolean",
                "exceptions" => "string",
                "enable_device" => "boolean",
                "enable_funnel" => "boolean",
                "last_edited" => "string"
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
			$data = Database::table($this->table['name'])
					->orderBy('id', 'desc')
					->findAll()->asArray();
			$os_val = [];
			$device_val = [];
			if (!empty($data))
			{
				foreach ($data as $key => $val)
				{
					$device_val = isset($val['device']) ? json_decode($val['device'], true) : [];
					$data[$key]['device_value'] = !empty($device_val) ? ucwords(implode(", ", $device_val)) : '';

					$os_val = isset($val['os']) ? json_decode($val['os'], true) : [];
					$data[$key]['os_value'] = !empty($os_val) ? ucwords(implode(", ", $os_val)) : '';

					// $data[$key]['affiliate_id_value'] = isset($val['affiliate_id']) ? json_decode($val['affiliate_id']) : [];
					// $data[$key]['sub_id_value'] = isset($val['sub_id']) ? json_decode($val['sub_id']) : [];
					$now = date_create();
					$last_edited = date_create($val['last_edited']);
					$diff = date_diff($now, $last_edited);
					$data[$key]['last_edited'] = 

						($diff->days < 1) ? 
							(($diff->h > 0) ? $diff->format('%h hours ago') : 'Few minutes ago') :
							$diff->format('%a day ago');
                                        
                                        if($data[$key]['affiliate_id_key'] == 'nid') {
                                            $data[$key]['network_id_value'] = $data[$key]['affiliate_id_value'];
                                            if($data[$key]['sub_id_key'] == 'affId' || $data[$key]['sub_id_key'] == 'afId') {
                                                $data[$key]['affiliate_id_value'] = $data[$key]['sub_id_value'];
                                            } else {
                                                unset($data[$key]['affiliate_id_value']);
                                            }
                                        }
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
				$validConfig = [];
				/* configurations */
				if ($key == 'configuration_id')
				{
					$config_ids = explode(',', $valueGet);	
					foreach ($config_ids as $index => $config_id)
					{
						$configurationInfo = Database::table('configurations')->where('id', '=', $config_id)->find();
						if(isset($configurationInfo->id)){
							$validConfig[] = $configurationInfo->id;
						}
						$valueGet = implode(',', $validConfig);
					}
				}

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

	public function checkAffiliateValue($inputKey)
	{
		$valueGet = $this->filterInput($inputKey);
		$updateValue = $valueGet;
		if ($inputKey == 'affiliate_id' || $inputKey == 'sub_id')
		{
			if (!empty($valueGet))
			{
				$jsonValue = json_decode($valueGet, 1);
				if (empty($jsonValue['key']))
				{
					$updateValue = "";
				}
			}
		}
		return $updateValue;
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
				$update_value = $this->checkAffiliateValue($key);
				$data[$key] = $row->{$key} = $update_value;
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
				$update_value = $this->checkAffiliateValue($key);
				$data[$key] = $row->{$key} = $update_value;
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
            $data_count = 0;

            foreach ($selectedIds as $key => $selectedId) {
                $res = Database::table($this->table['name'])->find($selectedId)->delete();
                if($res){
                    $deletedIds[] = $selectedId;
                }
                else{
                    $notDeletedIds[] = $selectedId;
                }
            }
			// Database::table($this->table['name'])->find($id)->delete();
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
				return (boolean) Request::form()->get($key, false);
			default:
				return Request::form()->get($key, '');
		}
	}

	private function isValidData($data)
	{
		return true;
	}

}
