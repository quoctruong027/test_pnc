<?php

namespace BulkGate\Extensions;

/**
 * @author Lukáš Piják 2020 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

interface IModule
{
    /**
     * @param string $path
     * @return string
     */
    public function getUrl($path = '');


    /** @return bool */
    public function statusLoad();


    /** @return bool */
    public function languageLoad();


    /** @return bool */
    public function storeLoad();


    /** @return string */
    public function product();


    /** @return string */
    public function url();


    /**
     * @param string|null $key
     * @return string|array
     */
    public function info($key = null);
}
