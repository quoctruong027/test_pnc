<?php

namespace BulkGate\Extensions\IO;

/**
 * @author Lukáš Piják 2020 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate;
use BulkGate\Extensions\ISettings;
use BulkGate\Extensions\Strict;

class ConnectionFactory extends Strict
{
    /** @var ISettings */
    private $settings;

    /** @var IConnection */
    private $io;


    public function __construct(ISettings $settings)
    {
        $this->settings = $settings;
    }


    /**
     * @param string $url
     * @param string $product
     * @return IConnection
     */
    public function create($url, $product)
    {
        if ($this->io === null)
        {
            if (extension_loaded('curl'))
            {
                $this->io = new cUrl($this->settings->load("static:application_id"), $this->settings->load("static:application_token"), $url, $product, $this->settings->load('main:language', 'en'));
            }
            else
            {
                $this->io = new FSock($this->settings->load("static:application_id"), $this->settings->load("static:application_token"), $url, $product, $this->settings->load('main:language', 'en'));
            }
        }
        return $this->io;
    }
}
