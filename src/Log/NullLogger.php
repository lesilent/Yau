<?php

namespace Yau\Log;

use Yau\Log\AbstractLogger;

/**
* Null logger
*/
class NullLogger extends AbstractLogger
{
/*=================================================================*/

/**
* Logs with an arbitrary level.
*
* @param mixed  $level
* @param string $message
* @param array  $context
*/
public function log($level, $message, array $context = array())
{

}

/*=================================================================*/
}