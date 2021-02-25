<?php

namespace BulkGate\Extensions\Hook\Channel;

/**
 * @author Lukáš Piják 2020 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

interface IChannel
{
    /**
     * @return bool
     */
    public function isActive();


    /**
     * @return array
     */
    public function toArray();
}
