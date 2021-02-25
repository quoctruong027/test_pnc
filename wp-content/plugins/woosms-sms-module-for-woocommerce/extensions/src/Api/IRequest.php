<?php

namespace BulkGate\Extensions\Api;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate;

interface IRequest
{
    /**
     * @return BulkGate\Extensions\Headers
     */
    public function getHeaders();
}
