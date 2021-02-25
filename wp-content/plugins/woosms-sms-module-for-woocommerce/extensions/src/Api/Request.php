<?php

namespace BulkGate\Extensions\Api;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Extensions;

class Request extends \stdClass implements IRequest
{
    /** @var array */
    private $data = array();

    /** @var Extensions\Headers */
    private $headers;


    public function __construct(Extensions\Headers $headers)
    {
        if (!isset($_SERVER['REQUEST_METHOD']) || (isset($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD']) !== 'post'))
        {
            throw new ConnectionException("Method Not Allowed", 405);
        }

        $this->headers = $headers;

        $content_type = $this->headers->get('Content-Type');

        $data = file_get_contents('php://input');

        if (is_string($data))
        {
            if ($content_type === 'application/json')
            {
                try
                {
                    $this->data = Extensions\Json::decode($data, Extensions\Json::FORCE_ARRAY);
                }
                catch (Extensions\JsonException $e)
                {
                    throw new ConnectionException('Bad Request', 400);
                }
            }
            else if ($content_type === 'application/zip')
            {
                $this->data = Extensions\Json::decode(Extensions\Compress::decompress($data), Extensions\Json::FORCE_ARRAY);
            }
            else
            {
                throw new ConnectionException('Bad Request', 400);
            }
        }
        else
        {
            throw new ConnectionException('Bad Request', 400);
        }
    }


    public function __get($name)
    {
        if (isset($this->data[$name]))
        {
            return $this->data[$name];
        }
        return null;
    }


    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }


    public function __isset($name)
    {
        return isset($this->data[$name]);
    }


    /**
     * @return Extensions\Headers
     */
    public function getHeaders()
    {
        return $this->headers;
    }
}
