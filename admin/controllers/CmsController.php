<?php

namespace Admin\Controller;

use Exception;
use Lazer\Classes\Database;
use Lazer\Classes\Helpers\Validate;
use Lazer\Classes\LazerException;
use Application\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;

class CmsController
{

	private $table;

	public function __construct()
	{
		$this->accessor = PropertyAccess::createPropertyAccessor();
		$this->table = array(
			'name' => 'cms',
			'attr' => array(
				"id" => "integer",
				"content_name" => "string",
				"content_body" => "string",
				"content_slug" => "string",
				"status"	   => "string",
				"last_edited"  => "string"
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
			foreach ($data as $key => $val)
			{
				$now = date_create();
				$last_edited = date_create($val['last_edited']);
				$diff = date_diff($now, $last_edited);
				$data[$key]['last_edited'] = 
					($diff->days < 1) ? 
						(($diff->h > 0) ? $diff->format('%h hours ago') : 'Few minutes ago') :
						$diff->format('%a day ago');
				$data[$key]['live_url'] = sprintf('%scms/%s/%s', Request::getOfferUrl(), $data[$key]['id'], $data[$key]['content_slug']);
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
				$data[$key] = $row->{$key} = $this->filterInput($key);
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
				$data[$key] = $row->{$key} = $this->filterInput($key);
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
				'data' => array(),
				'deletedIds' => $deletedIds,
				'notDeletedIds' => $notDeletedIds
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