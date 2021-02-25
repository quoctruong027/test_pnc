<?php

namespace BulkGate\Extensions\IO;

/**
 * @author Lukáš Piják 2020 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate, BulkGate\Extensions\Json;
use BulkGate\Extensions\Compress;
use BulkGate\Extensions\JsonException;

class Response extends \stdClass
{
    public function __construct($data, $content_type = null)
    {
        if (is_string($data))
        {
            if ($content_type === 'application/json')
            {
                try
                {
                    $result = Json::decode($data, Json::FORCE_ARRAY);

                    if (is_array($result))
                    {
                        $this->load((array) $result);
                    }
                }
                catch (JsonException $e)
                {
                    throw new InvalidResultException('Json parse error: '. $data);
                }
            }
            else if ($content_type === 'application/zip')
            {
                $result = Json::decode(Compress::decompress($data));

                if (is_array($result) || $result instanceof \stdClass)
                {
                    $this->load((array) $result);
                }
            }
            else
            {
                throw new InvalidResultException('Invalid content type'. $data);
            }
        }
        else if (is_array($data))
        {
            $this->load($data);
        }
        else
        {
            throw new InvalidResultException('Input not string (JSON)');
        }
    }


    public function load(array $array)
    {
        if (isset($array['signal']) && $array['signal'] === 'authenticate')
        {
            throw new AuthenticateException;
        }
        else
        {
            foreach ($array as $key => $value)
            {
                $this->{$key} = $value;
            }
        }
    }


    public function get($key)
    {
        $path = Key::decode($key);

        return array_reduce($path, function($prev, $now)
        {
            if ($now === Key::DEFAULT_VARIABLE)
            {
                return $prev;
            }
            else
            {
                if ($prev)
                {
                    if (is_array($prev))
                    {
                        return isset($prev[$now]) ? $prev[$now] : null;
                    }
                    else
                    {
                        return isset($prev->$now) ? $prev->$now : null;
                    }
                }
                else
                {
                    return null;
                }
            }
        }, $this->data);
    }


    public function remove($key)
    {
        if (isset($this->data))
        {
            list($reducer, $container, $variable) = Key::decode($key);

            if (isset($this->data->{$reducer}) && isset($this->data->{$reducer}->{$container}) && isset($this->data->{$reducer}->{$container}->{$variable}))
            {
                unset($this->data->{$reducer}->{$container}->{$variable});
            }
            else if (isset($this->data->{$reducer}) && isset($this->data->{$reducer}->{$container}))
            {
                unset($this->data->{$reducer}->{$container});
            }
            else if (isset($this->data->{$reducer}))
            {
                unset($this->data->{$reducer});
            }
        }
    }


    public function set($key, $value)
    {
        if (isset($this->data))
        {
            list($reducer, $container, $variable) = Key::decode($key);

            if (!isset($this->data[$reducer]))
            {
                $this->data[$reducer] = array();
            }

            if (!isset($this->data[$reducer][$container]))
            {
                $this->data[$reducer][$container] = array();
            }

            $this->data[$reducer][$container][$variable] = $value;
        }
    }
}
