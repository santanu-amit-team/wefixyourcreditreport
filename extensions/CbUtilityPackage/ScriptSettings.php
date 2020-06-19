<?php

namespace Extension\CbUtilityPackage;

use Application\Config;
use Application\Request;

class ScriptSettings extends Common
{
    public function render()
    {
        echo sprintf(
                '<script type="text/javascript">var cbUtilConfig = %s;</script>', json_encode(array(
            'disable_non_english_char_input' => Config::extensionsConfig(
                    'CbUtilityPackage.disable_non_english_char_input'
            ),
                ))
        );
    }

    public function convertNonEnglishChar()
    {
        $this->isEnable = Config::extensionsConfig('CbUtilityPackage.enable_non_english_char_convert');
        if (empty($this->isEnable))
        {
            return;
        }
        $formData = Request::form()->all();
        if (!empty($formData))
        {
            foreach ($formData as $key => $value)
            {
                Request::form()->set($key, $this->removeAccents($value));
            }
        }
    }

}
