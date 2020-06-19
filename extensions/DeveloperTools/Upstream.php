<?php

namespace Extension\DeveloperTools;

use Application\Http;
use Application\Registry;
use Application\Request;
use Application\Config;
use Exception;

class Upstream
{
    const ERR_NO_UNMATCHED = 'Branch is in sync with master';
    const ERR_UPSTREAM_EX  = 'Upstream error';
    const ERR_UPSTEAM_HASH = 'Upstream file hash error';
    const ERR_UPSTEAM_SYNC = 'No updates are available in Upstream';
    const ERR_PERM         = 'Permission error';
    const DOWNSTREAM_BK    = 'downstream';
    const COMMIT           = '.commit';
    const SYNCED           = 'Great! Framework has been upgraded to a newer version';

    public function __construct()
    {
        $this->updateSource = Http::get(
            Registry::system('systemConstants.REMOTE_FRAMEWORK_UPDATE_URL') . 'updates.php'
        );

        if (empty($this->updateSource)) {

            throw new Exception(self::ERR_UPSTEAM_SYNC);

        }

        $this->updateSource = json_decode($this->updateSource);

    }

    public function commitAndPull($mode = 'test')
    {
        try {
            $mode     = Request::query()->get('mode') ? Request::query()->get('mode') : 'pull';
            $repoHash = $this->getRepoFileHash();
            $diff     = array();

            foreach ($repoHash['hashes'] as $data) {
                if (@$data['hash'] != @md5_file($data['file'])) {
                    $diff[] = $data['file'];
                }
            }

            if (!empty($diff)) {

                $upstreamContents = Http::post(
                    Registry::system('systemConstants.REMOTE_FRAMEWORK_UPDATE_URL') . 'repo.php', $diff
                );
                $upstreamContents = json_decode($upstreamContents, true);

                if (empty($upstreamContents)) {

                    throw new Exception(self::ERR_UPSTREAM_EX);
                }

                $updatedFiles = array();

                foreach ($upstreamContents as $merge) {
                    if (!empty($merge['contents'])) {

                        $merge['file'] = str_replace(DS === '/' ? "\\" : '/', DS, $merge['file']);

                        $copy = STORAGE_DIR . DS . self::DOWNSTREAM_BK . DS . date(
                            'Y-m-d_H-i-s'
                        ) . DS . $merge['file'];

                        $base = dirname($copy);

                        is_dir($base) || mkdir($base, 0777, true) || die(
                            self::ERR_PERM . ' ' . json_encode(error_get_last())
                        );

                        @copy($merge['file'], $copy);
                        
                        /*External Directory Creation*/

                        $externalCopy = $merge['file'];
                        $externalBase = dirname($externalCopy);

                        is_dir($externalBase) || mkdir($externalBase, 0777, true) || die(
                            self::ERR_PERM . ' ' . json_encode(error_get_last())
                        );

                        @copy($merge['file'], $externalCopy);

                        /*External Directory Creation*/
                        
                        
                        $updatedFiles[$merge['file']] = array(
                            'status' => null,
                        );

                        if ($mode == 'pull') {
                            $status = @file_put_contents($merge['file'], $merge['contents']);

                            $updatedFiles[$merge['file']]['status'] = @$status;
                        }
                    }
                }

            }

            foreach ($repoHash['schemas'] as $data) {

                $fileName    = sprintf('%s%s', LAZER_DATA_PATH, $data['file']);
                $fileContent = @file_get_contents($fileName);

                if ($fileContent) {
                    $oldConfig = json_decode($fileContent, true);
                    if ($mode == 'pull' && is_array($data['schema'])) {
                        $oldConfig['schema'] = $data['schema'];
                        file_put_contents($fileName, json_encode($oldConfig), LOCK_EX);
                    }
                }
                
                $this->updateConfigData($data, $fileName, $mode);
            }

            if (empty($diff)) {
                throw new Exception(self::ERR_NO_UNMATCHED);
            }

            if ($mode == 'pull') {

                try {

                    $jsMinifier = \Lazer\Classes\Database::table('extensions')
                        ->where(
                            'extension_slug', '=', 'JsMinifier'
                        )
                        ->find()->asArray();

                    if (
                        !empty($jsMinifier)
                        &&
                        !empty($jsMinifier[0]['active'])
                        &&
                        $jsMinifier[0]['active'] === true
                    ) {

                        $compiler = new \Extension\JsMinifier\Compiler();
                        $minify   = $compiler->execute();
                    }
                } catch (Exception $e) {
                    $minify = $e;
                }
            }

            return array(
                'success'  => true,
                'type'     => 'alert',
                'message'  => self::SYNCED,
                'files'    => $updatedFiles,
                'minifier' => @$minify,
            );
        } catch (Exception $e) {
            return array(
                'success' => true,
                'type'    => 'alert',
                'message' => $e->getMessage(),
            );
        }

    }

    public function getRepoFileHash()
    {
        $repoHash = Http::get(Registry::system('systemConstants.REMOTE_FRAMEWORK_UPDATE_URL') . 'master/ajax.php/file-hash');

        if (empty($repoHash)) {

            throw new Exception(self::ERR_UPSTEAM_HASH);

        }

        return json_decode($repoHash, true);
    }

    protected function rglob($pattern, $flags = 0)
    {
        $files = glob($pattern, $flags);
        foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
            $files = array_merge($files, $this->rglob($dir . '/' . basename($pattern), $flags));
        }
        return $files;
    }
    
    public function updateConfigData($data, $fileName, $mode)
    {
        if(!empty($data) && $mode == 'pull')
        {            
            $fileName    = sprintf('%s%s', LAZER_DATA_PATH, str_replace('config','data',$data['file']));      
            $fileContent = @file_get_contents($fileName);
            if ($fileContent) {
                $currentDataKeys = json_decode($fileContent, true);
                foreach($currentDataKeys as $k => $oldKeys)
                {
                    if(count($data['schema']) != count($oldKeys))
                    {
                        $extraKeys = array_diff_key($data['schema'],$oldKeys);
                        array_walk_recursive(
                            $extraKeys,
                            function (&$v) {
                                $v = $this->assignValByDataType($v);                                    
                            }
                        );
                        $oldKeys = array_merge_recursive($oldKeys, $extraKeys);
                        //$currentDataKeys[$k] = $oldKeys;
                    }
                    
                    array_walk_recursive(
                        $oldKeys,
                        function (&$v) {
                            $v = is_null($v)? "" : $v;                                    
                        }
                    );
                    $currentDataKeys[$k] = $oldKeys;
                }

                file_put_contents($fileName, json_encode($currentDataKeys), LOCK_EX);
            }
              
        }
    }
    
    private function assignValByDataType($type)
    {
        switch ($type)
        {
            case 'boolean':
                $val = false;
                break;
            case 'integer':
                $val = 0;
                break;
            default :
                $val = "";
                break;
        }
        return $val;
    }
}
