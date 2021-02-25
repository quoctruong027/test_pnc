<?php

namespace BulkGate\Extensions\Database;

/**
 * @author Lukáš Piják 2020 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Extensions;

class Result extends Extensions\Iterator implements \Iterator
{
    /** @var array|mixed */
    protected $row = array();


    public function __construct(array $rows)
    {
        foreach ($rows as $key => $value)
        {
            if (is_array($value))
            {
                $this->array[$key] = new Extensions\Buffer($value);
            }
            else
            {
                $this->array[$key] = $value;
            }
        }

        $this->row = reset($this->array);
    }


    public function getRow()
    {
        return $this->row;
    }


    public function getRows()
    {
        return $this->array;
    }


    public function getNumRows()
    {
        return count($this->array);
    }
}
