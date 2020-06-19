<?php

namespace Admin\Controller;

use Application\Request;
use Application\Config;
use Exception;
use Lazer\Classes\Database;
use Lazer\Classes\Helpers\Validate;
use Lazer\Classes\LazerException;
use Symfony\Component\PropertyAccess\PropertyAccess;

class RotatorsController
{

    private $table;

    public function __construct()
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
        $this->table    = array(
            'name' => 'rotators',
            'attr' => array(
                'id'                    => 'integer',
                'label'                 => 'string',
                'company_name'          => 'string',
                'image_name'            => 'string',
                'address'               => 'string',
                'phone_no'              => 'string',
                'email'                 => 'string',
                'percentage'            => 'string',
                'configuration_mapping' => 'string',
            ),
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

    public function delete($id)
    {
        try
        {
            Database::table($this->table['name'])->find($id)->delete();
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

    public function getTotalPercentage($id = '')
    {
        try
        {
            $total_percentage = 0;
            if (!empty($id)) {
                $data = Database::table($this->table['name'])->where('id', '!=', $id)->findAll()->asArray();
            } else {
                $data = Database::table($this->table['name'])->findAll()->asArray();
            }

            if (!empty($data)) {
                foreach ($data as $key => $val) {
                    $total_percentage = $total_percentage + (!empty($val['percentage']) ? $val['percentage'] : 0);
                }
            }

            return array(
                'success' => true,
                'data'    => $total_percentage,
            );
        } catch (Exception $ex) {
            return array(
                'success'       => false,
                'data'          => 0,
                'error_message' => $ex->getMessage(),
            );
        }
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

    public function checkExtensions()
    {
        $result = array(
            'success'                 => true,
            'extensionRotarorsActive' => false,
        );

        if (Config::settings('enable_rotators') === true) {
            $extensions = Config::extensions();
            foreach ($extensions as $extension) {
                if ($extension['extension_slug'] !== 'Rotators') {
                    continue;
                }
                if ($extension['active'] === true) {
                    $result['extensionRotarorsActive'] = true;
                }
                break;
            }
        }

        return $result;

    }

}
