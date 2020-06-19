<?php

namespace Extension\JsMinifier;

use Application\Config;
use Application\Http;
use Application\Registry;
use Application\Resource;
use Exception;

class Compiler
{

    private $compilerUrl, $scripts, $customScripts;
    private $invalidFileNameError, $fileToSave, $versionControll;

    public function __construct()
    {
        $extensionScripts = array();
        $registry         = require sprintf('%s%sregistry.php', LIB_DIR, DS);

        $this->compilerUrl = "https://closure-compiler.appspot.com/compile";
        $this->scripts     = array();

        if (is_array(Registry::system('scripts'))) {
            $this->scripts = Registry::system('scripts');
        }
        if (is_array(Registry::extension('scripts'))) {
            foreach (Registry::extension('scripts') as $key => $value) {
                foreach ($value as $scriptKey => $script) {
                    $extensionScripts[$scriptKey] = 'extensions' . DS . $key . DS . $script;
                }
            }
        }
        $this->scripts = array_merge($this->scripts, $extensionScripts);

        $customScriptsString = Config::extensionsConfig('JsMinifier.custom_scripts');
        $customScriptsArray  = array_filter(
            array_map('trim', explode("\n", $customScriptsString))
        );
        $this->customScripts = array();
        $counter             = 0;
        foreach ($customScriptsArray as $customScript) {
            $this->customScripts[sprintf('__custom%s__', ++$counter)] = $customScript;
        }
        $this->scripts = array_merge($this->scripts, $this->customScripts);

        $this->invalidFileNameError = "Provide a valid JS file name.";
        $this->fileToSave           = BASE_DIR . DS . 'assets' . DS . 'dist' . DS . 'codebase.min.js';
        $this->versionControll      = BASE_DIR . DS . 'assets' . DS . 'dist' . DS . '.version';
    }

    public function execute()
    {
        $scriptPaths = array();
        foreach ($this->scripts as $key => $value) {
            array_push(
                $scriptPaths, BASE_DIR . DS . str_replace('/', DS, $value)
            );
        }
        $contents = '';
        $version  = 0;
        foreach ($scriptPaths as $scriptFile) {
            if (file_exists($scriptFile)) {
                $contents .= file_get_contents($scriptFile);
            }
        }
        $params = array(
            'js_code'           => $contents,
            'compilation_level' => 'SIMPLE_OPTIMIZATIONS',
            'output_format'     => 'json',
            'output_info'       => 'compiled_code',
        );
        $output = json_decode(Http::post($this->compilerUrl, http_build_query($params), $headers = array(
            'Content-Type: application/x-www-form-urlencoded',
        )), true);

        if (!empty($output['compiledCode'])) {
            file_put_contents($this->fileToSave, $output['compiledCode']);
            if (file_exists($this->versionControll)) {
                $version = (int) file_get_contents($this->versionControll);
            }
            file_put_contents(
                $this->versionControll, ++$version
            );
        } else {
            throw new Exception("Oops! compilation failed.");
        }
        return array(
            'success' => true,
            'message' => 'Compilation successful',
            'type'    => 'toast',
        );
    }

    public function updateResource()
    {
        if (!DEV_MODE && file_exists($this->fileToSave)) {
            Resource::unregisterAll('script');
            $saveFilePath = 'assets/dist/codebase.min.js';
            Resource::register('script', 'codebase.min', $saveFilePath);
        } else {
            foreach ($this->customScripts as $key => $value) {
                Resource::register('script', $key, $value);
            }
        }
    }

}
