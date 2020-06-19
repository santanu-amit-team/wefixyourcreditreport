<?php

namespace Extension\AdminLogs;

use Database\Connectors\ConnectionFactory;
use Exception;

class AdminLogs
{

    public function save()
    {
        
        if (!extension_loaded('pdo_sqlite') && !extension_loaded('pdo_sqlite'))
        {
            throw new Exception("Sqlite PDO extension is not installed.");
        }

        $adminLogsFilePath = STORAGE_DIR . DS . 'adminlogs.sqlite';

        if (!file_exists($adminLogsFilePath))
        {
            file_put_contents($adminLogsFilePath, '');
        }

        if (!is_writable($adminLogsFilePath))
        {
            throw new Exception(
            sprintf("File %s couldn't be created.", $adminLogsFilePath)
            );
        }
        
        $sql = "CREATE TABLE IF NOT EXISTS 'adminlogs' ("
                . "     id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,"
                . "     event TEXT DEFAULT NULL,"
                . "     logs TEXT DEFAULT NULL,"
                . "     email TEXT DEFAULT NULL,"
                . "     ipAddress TEXT DEFAULT NULL,"
                . "     created_on DATETIME DEFAULT NULL"
                . ")";

        $this->getDatabaseConnection()->query($sql);
        
        return true;
    }
    
    private function getDatabaseConnection()
    {
        $factory = new ConnectionFactory();
        return $factory->make(array(
                    'driver' => 'sqlite',
                    'database' => STORAGE_DIR . DS . 'adminlogs.sqlite',
        ));
    }

}
