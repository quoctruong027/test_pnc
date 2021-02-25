<?php

namespace BulkGate\Extensions;

/**
 * @author Lukáš Piják 2020 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Extensions\IO\Response;
use BulkGate\Extensions\IO\AuthenticateException;

class Synchronize extends Strict
{
    /** @var ISettings */
    private $settings;

    /** @var IO\IConnection */
    private $connection;


    public function __construct(ISettings $settings, IO\IConnection $connection)
    {
        $this->settings = $settings;
        $this->connection = $connection;
    }


    public function run($url, $now = false)
    {
        try
        {
            if ($now || $this->settings->load('static:synchronize', 0) < time() && $this->settings->load('static:application_id', false))
            {
                $self = $this;
                $this->synchronize(function ($module_settings) use ($url, $self) {
                    return $self->connection->run(new IO\Request($url, array('__synchronize' => $module_settings), true, (int) $self->settings->load('main:synchronize_timeout', 6)));
                });
            }
        }
        catch (AuthenticateException $e)
        {
            $this->settings->delete('static:application_token');
        }
    }


    public function synchronize($callback)
    {
        if (is_callable($callback))
        {
            $module_settings = $this->settings->synchronize();
            $server_settings = call_user_func($callback, $module_settings);

            if((isset($server_settings->exception) && $server_settings->exception) || (isset($server_setting->error) && !empty($server_settings->error)))
            {
                return $server_settings;
            }
            else
            {
                if ($server_settings instanceof Response)
                {
                    foreach ((array) $server_settings->get(':synchronize:') as $server_setting)
                    {
                        $key = $this->getKey($server_setting->scope, $server_setting->key);

                        if (isset($module_settings[$key]))
                        {
                            $server_setting->datetime = isset($server_setting->datetime) ? (int)$server_setting->datetime : 0;
                            $module_settings[$key]->datetime = isset($module_settings[$key]->datetime) ? (int)$module_settings[$key]->datetime : 0;

                            if ($server_setting->datetime >= $module_settings[$key]->datetime)
                            {
                                $this->settings->set($key, $server_setting->value, array('type' => isset($server_setting->type) ? $server_setting->type : 'text', 'datetime' => $server_setting->datetime, 'synchronize_flag' => $server_setting->synchronize_flag, 'order' => isset($server_setting->order) ? $server_setting->order : 0,));
                            }
                        }
                        else
                        {
                            $this->settings->set($key, $server_setting->value, array('type' => isset($server_setting->type) ? $server_setting->type : 'text', 'datetime' => isset($server_setting->datetime) ? $server_setting->datetime : 0, 'order' => isset($server_setting->order) ? $server_setting->order : 0, 'synchronize_flag' => isset($server_setting->synchronize_flag) ? $server_setting->synchronize_flag : 'none'));
                        }
                    }

                    $this->settings->delete();

                    $this->settings->set('static:synchronize', time() + $this->settings->load('main:synchronize_interval', 21600 /* 6 hours */));
                }
            }
            $server_settings->remove(':synchronize:');

            return $server_settings;
        }
        return false;
    }

    private function getKey($scope, $key)
    {
        return (string)$scope . ':' . (string)$key;
    }
}
