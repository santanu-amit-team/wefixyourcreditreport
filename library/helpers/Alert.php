<?php

namespace Application\Helper;

use Database\Connectors\ConnectionFactory;
use Exception;

class Alert
{
    public static function createConnection()
    {
        $database = STORAGE_DIR . DS . 'alerts.sqlite';
        try
        {
            if (!extension_loaded('pdo_sqlite')) {
                throw new Exception('PDO extension does not exists');
            }
            if (!file_exists($database)) {
                file_put_contents($database, '');
            }
            if (!is_writable($database)) {
                throw new Exception('Check write permission of database file');
            }
            $factory    = new ConnectionFactory();
            $connection = $factory->make(array(
                'driver'   => 'sqlite',
                'database' => $database,
            ));
            return $connection;
        } catch (Exception $ex) {
            throw ($ex);
        }
    }

    public static function createTable()
    {
        try
        {
            $sql = "CREATE TABLE IF NOT EXISTS 'alert' ("
                . "'id' INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,"
                . "'identifier' TEXT,"
                . "'text' TEXT,"
                . "'text_hash' TEXT,"
                . "'read' INTEGER DEFAULT 0 ,"
                . "'type' TEXT DEFAULT 0,"
                . "'alert_handler' TEXT,"
                . "'created_at' TEXT,"
                . "'updated_at' TEXT )";

            $query = self::createConnection()->query($sql);
            return true;
        } catch (Exception $ex) {
            return array(
                'success'       => false,
                'error_message' => $ex->getMessage(),
            );
        }
    }

    public static function insertData($data)
    {
        try
        {
            self::createTable();
            $query = self::createConnection()->table('alert')
                ->select(
                    'id', 'identifier', 'text', 'text_hash', 'read',
                    'type', 'alert_handler', 'created_at'
                )
                ->where('identifier', '=', $data['identifier'])
                ->where('text_hash', '=', md5($data['text']))
                ->where('read', '=', 0)
                ->get();
            if (!empty($query)) {
                return array(
                    'success' => false,
                    'message' => 'Data already there',
                );
            }
            self::createConnection()->table('alert')->insert(array(
                'identifier' => $data['identifier'],
                'text'       => $data['text'],
                'text_hash'  => md5($data['text']),
                'type'       => $data['type'],
                // 'alert_handler' => $data['alert_handler'],
                'created_at' => date('Y-m-d H:i:s', time()),
                'updated_at' => date('Y-m-d H:i:s', time()),
            ));
            return;
        } catch (Exception $ex) {
            return array(
                'success'       => false,
                'error_message' => $ex->getMessage(),
            );
        }
    }

    public static function updateData($id)
    {
        try
        {
            self::createTable();
            $data['updated_at'] = date('Y-m-d H:i:s', time());
            $data['read']       = 1;
            self::createConnection()->table('alert')
                ->where('id', '=', $id)
                ->update($data);

            return array(
                'success' => true,
                'data'    => self::getData(),
            );
        } catch (Exception $ex) {
            return array(
                'success'       => false,
                'error_message' => $ex->getMessage(),
            );
        }
    }

    public static function getData($id = null)
    {
        try
        {
            self::createTable();
            
            $sql = "select * from alert where read=0 and (text LIKE '%Cron job%' OR "
                    . "text LIKE '%database credential%' OR"
                    . " text LIKE '%encryption key%')";
            $list = self::createConnection()->query($sql)->fetchAll();
            
            return array(
                'success' => true,
                'data'    => $list,
            );
        } catch (Exception $ex) {
            return array(
                'success'       => false,
                'error_message' => $ex->getMessage(),
            );
        }
    }

    public static function removeData($data)
    {
        self::createConnection()->table('alert')
            ->where('identifier', '=', $data['identifier'])
            ->where('text_hash', '=', md5($data['text']))
            ->where('type', '=', $data['type'])
            ->update(array(
                'read' => 1,
            ));
    }

}
