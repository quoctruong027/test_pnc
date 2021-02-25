<?php

namespace BulkGate\Extensions\Api;

/**
 * @author Lukáš Piják 2020 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate;
use BulkGate\Extensions\Compress;
use BulkGate\Extensions\Json;
use BulkGate\Extensions\Strict;

class Response extends Strict implements IResponse
{
    /** @var mixed */
    private $payload;

    /** @var string */
    private $contentType;


    public function __construct($payload, $compressed = false)
    {
        $this->payload = $payload;
        $this->contentType = $compressed ? 'application/zip' : 'application/json';
    }


    public function getPayload()
    {
        return $this->payload;
    }


    public function getContentType()
    {
        return $this->contentType;
    }


    public function send()
    {
        header("Content-Type: {$this->contentType}; charset=utf-8");

        if ($this->contentType === 'application/zip')
        {
            echo Compress::compress(Json::encode($this->payload));
        }
        else
        {
            echo Json::encode($this->payload);
        }
    }
}
