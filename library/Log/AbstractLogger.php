<?php

namespace Yau\Log;

use Yau\Log\LoggerInterface;
use Yau\Log\LogLevel;

/**
* Abstract logger class
*
*/
abstract class AbstractLogger implements LoggerInterface
{
/*=================================================================*/

/**
* System is unusable.
*
* @param string $message
* @param array  $context
*/
public function emergency($message, array $context = array())
{
	$this->log(LogLevel::EMERGENCY, $message, $context);
}

/**
* Action must be taken immediately.
*
* @param string $message
* @param array  $context
*/
public function alert($message, array $context = array())
{
	$this->log(LogLevel::ALERT, $message, $context);
}

/**
* Critical conditions.
*
* Example: Application component unavailable, unexpected exception.
*
* @param string $message
* @param array  $context
*/
public function critical($message, array $context = array())
{
	$this->log(LogLevel::CRITICAL, $message, $context);
}

/**
* Runtime errors that do not require immediate action but should typically
* be logged and monitored.
*
* @param string $message
* @param array  $context
*/
public function error($message, array $context = array())
{
	$this->log(LogLevel::ERROR, $message, $context);
}

/**
* Exceptional occurrences that are not errors.
*
* Example: Use of deprecated APIs, poor use of an API, undesirable things
* that are not necessarily wrong.
*
* @param string $message
* @param array  $context
*/
public function warning($message, array $context = array())
{
	$this->log(LogLevel::ERROR, $message, $context);
}

/**
* Normal but significant events.
*
* @param string $message
* @param array  $context
*/
public function notice($message, array $context = array())
{
	$this->log(LogLevel::NOTICE, $message, $context);
}

/**
* Interesting events.
*
* Example: User logs in, SQL logs.
*
* @param string $message
* @param array  $context
*/
public function info($message, array $context = array())
{
	$this->log(LogLevel::INFO, $message, $context);
}

/**
* Detailed debug information.
*
* @param string $message
* @param array  $context
*/
public function debug($message, array $context = array())
{
	$this->log(LogLevel::DEBUG, $message, $context);
}

/**
* Logs with an arbitrary level.
*
* @param mixed  $level
* @param string $message
* @param array  $context
*/
public function log($level, $message, array $context = array());

/*=================================================================*/
}