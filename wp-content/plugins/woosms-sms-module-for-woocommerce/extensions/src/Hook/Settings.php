<?php

namespace BulkGate\Extensions\Hook;

/**
 * @author Lukáš Piják 2020 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate;
use BulkGate\Extensions\Iterator;

class Settings extends Iterator
{
    public function __construct(array $data)
    {
        $settings = array();

        foreach ($data as $type => $channel)
        {
            switch ($type)
            {
                case 'sms':
                    $settings[$type] = new Channel\Sms((array) $channel);
                break;
                default:
                    $settings[$type] = new Channel\DefaultChannel((array) $channel);
                break;
            }
        }

        parent::__construct($settings);
    }


    public function toArray()
    {
        $output = array();

        /** @var Channel\IChannel $item */
        foreach ($this->array as $key => $item)
        {
            if ($item->isActive())
            {
                $output[$key] = $item->toArray();
            }
        }
        return $output;
    }
}
