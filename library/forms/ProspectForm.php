<?php

namespace Application\Form;

use Application\Helper\FormHandler;

class ProspectForm extends FormHandler
{
	protected static $fields = array();
	
    protected $name = 'prospect';

    public function __construct()
    {
        self::$fields = ProspectFields::$data;
        parent::__construct();

        if (!empty($this->data['phone'])) {
            $this->data['phone'] = str_replace(
                ['-', ' ', '(', ')', '+'], '', $this->data['phone']
            );
        }
        
        parent::saveToSession();
    }

    public static function __callStatic($methodName, $arguments)
    {

        if (empty(self::$fields) || !is_array(self::$fields)) {
            self::$fields = ProspectFields::$data;
        }

        if (method_exists(get_called_class(), $methodName)) {
            return call_user_func_array(
                array(get_called_class(), $methodName), $arguments
            );
        }

        throw new Exception(
            sprintf(
                '%s::%s method not found!', get_called_class(), $methodName
            )
        );
    }
}
