<?php

namespace Application\Helper;

use Application\Extension;
use Application\Request;
use Application\Response;
use Application\Session;

class FormHandler
{

    protected $name, $data, $request, $errors, $extension;

    public function __construct()
    {
        static::$fields = is_array(static::$fields) ? static::$fields : array();
        $this->form     = Request::form();
        $this->data     = array();
        $this->errors   = array();
        $this->load();
        $this->extension = Extension::getInstance();
    }

    protected function load()
    {
        foreach (static::$fields as $fieldName => $fieldMeta) {
            $this->data[$fieldName] = null;

            if ($this->form->has($fieldName)) {
                $this->data[$fieldName] = $this->form->get($fieldName);
            } else if (!empty($fieldMeta['aliases'])) {
                foreach ($fieldMeta['aliases'] as $alias) {
                    if ($this->form->has($alias)) {
                        $this->data[$fieldName] = trim(
                            $this->form->get($alias)
                        );
                        break;
                    }
                }
            }

            if ($this->data[$fieldName] === null) {
                $this->data[$fieldName] = Session::get(
                    sprintf('customer.%s', $fieldName), null
                );
            } else if (
                $fieldName === 'cardNumber' &&
                Session::has('steps.meta.isPrepaidFlow')
            ) {
                Session::remove('steps.meta.isPrepaidFlow');
            }
        }

        $customFormData    = Request::form()->get('custom', array());
        $customSessionData = Session::get('custom', array());
        if (is_array($customFormData)) {
            Session::set('custom', array_replace_recursive(
                $customSessionData, $customFormData
            ));
        }

    }

    public function saveToSession()
    {
        foreach (static::$fields as $fieldName => $fieldMeta) {
            if ($this->data[$fieldName] !== null) {
                Session::set(
                    sprintf('customer.%s', $fieldName), $this->data[$fieldName]
                );
            }
        }
    }

    protected function mergeWithSession()
    {
        foreach (static::$fields as $fieldName => $fieldMeta) {
            if ($this->data[$fieldName] === null) {
                $this->data[$fieldName] = Session::get(
                    sprintf('customer.%s', $fieldName)
                );
            }
            if ($this->data[$fieldName] !== null) {
                Session::set(
                    sprintf('customer.%s', $fieldName), $this->data[$fieldName]
                );
            }
        }
    }

    public function getSafeData()
    {
        foreach (static::$fields as $fieldName => $fieldMeta) {
            if ($this->isRequired($fieldName) && $this->data[$fieldName] === null) {
                $label = empty(static::$fields[$fieldName]['label']) ?
                $fieldName : static::$fields[$fieldName]['label'];
                $this->errors[$fieldName] = $label . ' is a required field.';
            }
        }
        if (!empty($this->errors)) {
            Response::send(array(
                'success' => false,
                'data'    => Request::form()->all(),
                'errors'  => $this->errors,
            ));
        }

        $this->extension->performEventActions('afterBasicFormValidation');

        return array_filter($this->data, function ($value) {
            return $value === null ? false : true;
        });
    }

    public function isRequired($fieldName)
    {
        if (empty(static::$fields[$fieldName]['required'])) {
            return false;
        }
        if (is_bool(static::$fields[$fieldName]['required'])) {
            return static::$fields[$fieldName]['required'];
        }
        if (is_array(static::$fields[$fieldName]['required'])) {
            $meta = static::$fields[$fieldName]['required'];
            return $this->data[$meta[0]] === $meta[1];
        }
        return false;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    protected static function getSessionData()
    {
        $data = array();
        foreach (array_keys(static::$fields) as $fieldName) {
            $data[$fieldName] = Session::get(
                sprintf('customer.%s', $fieldName)
            );
        }
        return $data;
    }

}
