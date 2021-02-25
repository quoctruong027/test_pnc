<?php

namespace BulkGate\Extensions\IO;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

class Key
{
    /** @var string */
    const DEFAULT_REDUCER = "_generic";

    /** @var string */
    const DEFAULT_CONTAINER = "server";

    /** @var string */
    const DEFAULT_VARIABLE = "_empty";


    static public function decode($key)
    {
        if (preg_match("~^(?<reducer>[a-zA-Z0-9_-]*):(?<container>[a-zA-Z0-9_-]*):(?<name>[a-zA-Z0-9_-]*)$~", $key, $match))
        {
            return array($match["reducer"] ?: self::DEFAULT_REDUCER, $match["container"] ?: self::DEFAULT_CONTAINER, $match["name"] ?: self::DEFAULT_VARIABLE);
        }
        else if (preg_match("~^(?<container>[a-zA-Z0-9_-]*):(?<name>[a-zA-Z0-9_-]*)$~", $key, $match))
        {
            return array(self::DEFAULT_REDUCER, $match["container"] ?: self::DEFAULT_CONTAINER, $match["name"] ?: self::DEFAULT_VARIABLE);
        }
        else if (preg_match("~^(?<name>[a-zA-Z0-9_-]*)$~", $key, $match))
        {
            return array(self::DEFAULT_REDUCER, self::DEFAULT_CONTAINER, $match["name"] ?: self::DEFAULT_VARIABLE);
        }
        throw new InvalidResultException;
    }


    static public function encode($name, $container, $reducer)
    {
        return $reducer . ':' . $container . ':' . $name;
    }
}
