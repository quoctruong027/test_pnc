<?php

namespace BulkGate\Extensions;

/**
 * @author Lukáš Piják 2020 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

class JsonResponse extends Strict
{
    public static function send($data)
    {
        header('Content-Type: application/json');
        echo Json::encode($data);
        exit;
    }
}
