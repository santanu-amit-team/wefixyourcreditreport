<?php

namespace Application\Form;

use Application\Helper\FormHandler;

class CheckoutForm extends FormHandler
{

    protected static $fields = array();

    protected $name = 'checkout';

    public function __construct()
    {
        self::$fields = CheckoutFields::$data;
        parent::__construct();

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
            self::$fields = CheckoutFields::$data;
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
