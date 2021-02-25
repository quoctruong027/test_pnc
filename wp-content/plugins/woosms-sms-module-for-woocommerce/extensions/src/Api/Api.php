<?php

namespace BulkGate\Extensions\Api;

/**
 * @author Lukáš Piják 2020 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Extensions;
use BulkGate\Extensions\Database\IDatabase;
use BulkGate\Extensions\ISettings;

abstract class Api extends Extensions\Strict
{
    /** @var IDatabase */
    protected $database;

    /** @var ISettings */
    protected $settings;


    public function __construct($action, IRequest $data, IDatabase $database, ISettings $settings)
    {
        $this->database = $database;
        $this->settings = $settings;

        $method = 'action'.ucfirst($action);

        if (method_exists($this, $method))
        {
            call_user_func_array(array($this, $method), array($data));
        }
        else
        {
            throw new ConnectionException('Not Found', 404);
        }
    }


    public function sendResponse(IResponse $response)
    {
        $response->send();
        exit;
    }
}
