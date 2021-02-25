<?php

namespace BulkGate\Extensions\IO;

/**
 * @author Lukáš Piják 2020 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

interface IConnection
{
    /**
     * @param Request $request
     * @return Response
     */
    public function run(Request $request);
}
