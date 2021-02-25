<?php

namespace BulkGate\Extensions\Hook;

/**
 * @author Lukáš Piják 2020 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

interface ILoad
{
    /**
     * @param Variables $variables
     * @return void
     */
    public function load(Variables $variables);
}
