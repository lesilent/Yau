<?php declare(strict_types = 1);

namespace Yau\Log;

use Yau\Log\LoggerInterface;

/**
 * Describes a logger-aware instance
 */
interface LoggerAwareInterface
{
/*=================================================================*/

/**
 * Sets a logger instance on the object
 *
 * @param LoggerInterface $logger
 */
public function setLogger(LoggerInterface $logger):void;

/*=================================================================*/
}