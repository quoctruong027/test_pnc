<?php

namespace BulkGate\Extensions\Hook;

/**
 * @author Lukáš Piják 2020 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Extensions\Database;

interface IExtension
{
    /**
     * @param Database\IDatabase $database
     * @param Variables $variables
     * @return void
     */
    public function extend(Database\IDatabase $database, Variables $variables);
}
