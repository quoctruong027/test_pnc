<?php

namespace BulkGate\Extensions;

/**
 * @author Lukáš Piják 2020 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

class Exception extends \Exception
{
}


class StrictException extends Exception
{
}


class JsonException extends Exception
{
}


class InvalidKeyException extends Exception
{
}


class ServiceNotFoundException extends Exception
{
}
