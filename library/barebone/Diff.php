<?php

namespace Application;

use Application\Helper\Provider;

class Diff
{
    public function getLocalFileHash()
    {
        $updateSource = Http::get(
            Registry::system('systemConstants.REMOTE_URL') . 'updates.php'
        );

        $updateSource = json_decode($updateSource, true);

        foreach ($updateSource as $path) {
            foreach (Provider::rglob($path . "*.*") as $filename) {
                $hashes[] = array(
                    'file' => $filename,
                    'hash' => md5_file($filename),
                );
            }
        }

        $schemas = array();
        foreach (glob(LAZER_DATA_PATH . '*.config.json') as $path) {

            $config = file_get_contents($path);
            $config = json_decode($config, true);

            $schemas[] = array(
                'file'   => str_replace(LAZER_DATA_PATH, '', $path),
                'schema' => @$config['schema'],
            );
        }

        return array(
            'hashes'  => $hashes,
            'schemas' => $schemas,
        );
    }
}
