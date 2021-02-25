<?php

namespace BulkGate\Extensions;

/**
 * @author Lukáš Piják 2020 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Extensions;

class Iterator extends Extensions\Strict implements \Iterator
{
    /** @var array */
    protected $array = array();

    /** @var int */
    private $position = 0;


    public function __construct(array $rows)
    {
        $this->array = $rows;
        $this->position = 0;
    }


    function get($key)
    {
        return isset($this->array[$key]) ? $this->array[$key] : null;
    }


    function set($key, $value)
    {
        return $this->array[$key] = $value;
    }


    function rewind()
    {
        reset($this->array);
    }


    function current()
    {
        return current($this->array);
    }


    function key()
    {
        return key($this->array);
    }


    function next()
    {
        next($this->array);
    }


    function valid()
    {
        return key($this->array) !== null;
    }


    function count()
    {
        return count($this->array);
    }


    function add($value)
    {
        $this->array[] = $value;
    }
}
