<?php

namespace BulkGate\Extensions;

/**
 * @author Lukáš Piják 2020 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

interface ICustomers
{
    /**
     * @param array $filter
     * @return array
     */
    public function loadCount(array $filter = array());


    /**
     * @param array $filter
     * @return array
     */
    public function load(array $filter = array());
}
