<?php

namespace BulkGate\Extensions\Api;

/**
 * @author Lukáš Piják 2020 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 */

use BulkGate\Extensions;

class ConnectionException extends Extensions\Exception
{
}

class InvalidRequestException extends ConnectionException
{
}

class UnknownActionException extends ConnectionException
{
}

class MethodNotAllowedException extends ConnectionException
{
}
