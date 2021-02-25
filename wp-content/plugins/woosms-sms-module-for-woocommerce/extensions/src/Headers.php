<?php

namespace BulkGate\Extensions;

/**
 * @author Lukáš Piják 2020 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate;

class Headers extends Strict
{
    /** @var array */
    private $headers = array();


    /**
     * @param null|string $name
     * @param null|string $default
     * @return array|null|string
     */
    public function get($name = null, $default = null)
    {
        if (empty($this->headers))
        {
            $this->init();
        }

        $name = strtolower($name);

        if ($name !== null)
        {
            return isset($this->headers[$name]) ? $this->headers[$name] : $default;
        }
        return $this->headers;
    }


    private function init()
    {
        if (function_exists('apache_request_headers'))
        {
            $this->headers = array_change_key_case(apache_request_headers(), CASE_LOWER);
        }
        else
        {
            foreach ($_SERVER as $key => $value)
            {
                if (strncmp($key, 'HTTP_', 5) === 0)
                {
                    $this->headers[
                        strtolower(
                            strtr(
                                substr($key, 5), '_', '-'
                            )
                        )
                    ] = $value;
                }
            }
        }
    }
}
