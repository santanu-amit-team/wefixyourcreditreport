<?php

namespace Application\Form;

use Application\Helper\FormHandler;

class UpsellForm extends FormHandler
{
    protected static $fields = array();

    protected $name = 'upsell';

    public function __construct()
    {
        self::$fields = array_replace_recursive(
            ProspectFields::$data,
            CheckoutFields::$data
        );
        parent::__construct();

        if (!empty($this->data['phone'])) {
            $this->data['phone'] = str_replace(
                ['-', ' ', '(', ')', '+'], '', $this->data['phone']
            );
        }
        
        if (!empty($this->data['cardNumber'])) {
            $this->data['cardNumber'] = str_replace(
                ['-', ' '], '', $this->data['cardNumber']
            );
        }

        parent::saveToSession();
    }

    public static function __callStatic($methodName, $arguments)
    {

        if (empty(self::$fields) || !is_array(self::$fields)) {
            self::$fields = araay_replace_recursive(
                ProspectFields::$data,
                CheckoutFields::$data
            );
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
