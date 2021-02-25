<?php

namespace BulkGate\Extensions\Api;

/**
 * @author Lukáš Piják 2020 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate;

interface IResponse
{
    /**
     * @return void
     */
    public function send();
}
