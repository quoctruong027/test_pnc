<?php

namespace BulkGate\Extensions;

/**
 * @author Lukáš Piják 2020 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate;

class Settings extends Strict implements ISettings
{
    /** @var array */
    public $data = array();

    /** @var Database\IDatabase */
    private $db;

    /** @var array */
    private $types = array('text','int','float','bool','json');

    /** @var array */
    private $flags = array('none','add','change','delete');


    public function __construct(Database\IDatabase $database)
    {
        $this->db = $database;
    }


    public function load($settings_key, $default = false, $reload = false)
    {
        list($scope, $key) = Key::decode($settings_key);

        if (isset($this->data[$scope]) && !$reload)
        {
            if (isset($this->data[$scope][$key]))
            {
                return $this->data[$scope][$key];
            }
            else if (isset($this->data[$scope]) && !isset($this->data[$scope][$key]) && $key !== null)
            {
                return $default;
            }
            else
            {
                return $this->data[$scope];
            }
        }
        else
        {
            $result = $this->db->execute(
                $this->db->prepare(
                    'SELECT * FROM `'.$this->db->table('bulkgate_module').'` WHERE `scope` = %s AND `synchronize_flag` != "delete" ORDER BY `order`',
                    array($scope)
                )
            );

            if($result->getNumRows() > 0)
            {
                foreach ($result as $item)
                {
                    switch($item->type)
                    {
                        case 'text':
                            $this->data[$scope][$item->key] = (string) $item->value;
                        break;
                        case 'int':
                            $this->data[$scope][$item->key] = (int) $item->value;
                        break;
                        case 'float':
                            $this->data[$scope][$item->key] = (float) $item->value;
                        break;
                        case 'bool':
                            $this->data[$scope][$item->key] = (bool) $item->value;
                        break;
                        case 'json':
                            try
                            {
                                $this->data[$scope][$item->key] = BulkGate\Extensions\Json::decode($item->value);
                            }
                            catch (BulkGate\Extensions\JsonException $e)
                            {
                                $this->data[$scope][$item->key] = null;
                            }

                        break;
                    }
                }
            }
            else
            {
                $this->data[$scope] = false;
            }
            return $this->load($settings_key, $default);
        }
    }


    public function set($settings_key, $value, array $meta = array())
    {
        if (!isset($meta['datetime']))
        {
            $meta['datetime'] = time();
        }

        list($scope, $key) = Key::decode($settings_key);

        $result = $this->db->execute(
            $this->db->prepare(
                'SELECT * FROM `'.$this->db->table('bulkgate_module').'` WHERE `scope` = %s AND `key` = %s',
                array($scope, $key)
            )
        );

        if ($result->getNumRows() > 0)
        {
            $this->db->execute(
                $this->db->prepare(
                    'UPDATE `'.$this->db->table('bulkgate_module').'` SET value = %s, `datetime` = %s '.$this->parseMeta($meta).' WHERE `scope` = %s AND `key` = %s',
                    array($value, $meta['datetime'], $scope, $key)
                ));
        }
        else
        {
            $this->db->execute(
                $this->db->prepare('
                        INSERT INTO `'.$this->db->table('bulkgate_module').'` SET 
                            `scope`= %s,
                            `key`= %s,
                            `value`= %s'.$this->parseMeta($meta).'
                ', array($scope, $key, $value))
            );
        }
    }


    public function delete($settings_key = null)
    {
        if ($settings_key === null)
        {
            $this->db->execute('DELETE FROM `'.$this->db->table('bulkgate_module').'` WHERE `synchronize_flag` = "delete"');
        }
        else
        {
            list($scope, $key) = Key::decode($settings_key);

            $this->db->execute(
                $this->db->prepare('DELETE FROM `'.$this->db->table('bulkgate_module').'` WHERE `scope` = %s AND `key` = %s',
                array($scope, $key))
            );
        }
    }


    public function synchronize()
    {
        $output = array();

        $result = $this->db->execute('SELECT * FROM `'.$this->db->table('bulkgate_module').'` WHERE `scope` != "static"')->getRows();

        foreach ($result as $row)
        {
            $output[$row->scope.':'.$row->key] = $row;
        }

        return $output;
    }


    public function install()
    {
        $this->db->execute("
            CREATE TABLE IF NOT EXISTS `".$this->db->table('bulkgate_module')."` (
              `scope` varchar(50) NOT NULL DEFAULT 'main',
              `key` varchar(50) NOT NULL,
              `type` enum('text','int','float','bool','json') DEFAULT 'text',
              `value` text NOT NULL,
              `datetime` bigint(20) DEFAULT NULL,
              `order` int(11) NOT NULL DEFAULT '0',
              `synchronize_flag` enum('none','add','change','delete') NOT NULL DEFAULT 'none',
              PRIMARY KEY (`scope`,`key`)
            ) DEFAULT CHARSET=utf8;
        ");
        $this->set('static:synchronize', 0, array('type' => 'int'));
    }


    public function uninstall()
    {
        if ($this->load('main:delete_db', false))
        {
            $this->db->execute("DROP TABLE IF EXISTS `" . $this->db->table('bulkgate_module') . "`");
        }
    }


    private function parseMeta(array $meta)
    {
        $output = array();

        foreach ($meta as $key => $item)
        {
            switch ($key)
            {
                case 'type':
                    $output[] = $this->db->prepare('`type`= %s', array($this->checkType($item)));
                break;
                case 'datetime':
                    $output[] = $this->db->prepare('`datetime`= %s', array($this->formatDate($item)));
                break;
                case 'order':
                    $output[] = $this->db->prepare('`order`= %s', array((int) $item));
                break;
                case 'synchronize_flag':
                    $output[] = $this->db->prepare('`synchronize_flag`= %s', array($this->checkFlag($item)));
                break;
            }
        }
        return count($output) > 0 ? ','.implode(',', $output) : '';
    }


    private function formatDate($date)
    {
        if ($date instanceof \DateTime)
        {
            return $date->getTimestamp();
        }
        else if (is_string($date))
        {
            return strtotime($date);
        }
        else if (is_int($date))
        {
            return $date;
        }
        return time();
    }


    private function checkType($type, $default = 'text')
    {
        if (in_array((string) $type, $this->types))
        {
            return $type;
        }
        return $default;
    }


    private function checkFlag($flag, $default = 'none')
    {
        if (in_array((string) $flag, $this->flags))
        {
            return $flag;
        }
        return $default;
    }
}
