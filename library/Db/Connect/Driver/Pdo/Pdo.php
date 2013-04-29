<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
*/

namespace Yau\Db\Connect\Driver\Pdo;

use Yau\Db\Connect\Driver\DriverInterface;
use Yau\Db\Connect\Exception\InvalidArgumentException;

/**
* Class for connecting to a database using PDO
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
* @see      PDO
* @link     http://www.php.net/manual/en/ref.pdo.php
*/
class Pdo implements DriverInterface
{
/*=======================================================*/

/**
* Return the driver options from the connection parameters
*
* @param  array $params
* @return array
*/
protected static function getDriverOptions($params)
{
	$driver_options = (isset($params['driver_options']))
		? $params['driver_options']
		: array();
	if (!empty($params['persistent']))
	{
		$driver_options[PDO::ATTR_PERSISTENT] = TRUE;
	}
	if (isset($params['timeout']))
	{
		$driver_options[PDO::ATTR_TIMEOUT] = $params['timeout'];
	}
	return $driver_options;
}

/**
* Connect to a database using parameters
*
* @param  array  $params associative array containing the information for
*                        connecting to the database
* @return object a PDO object
* @throws Exception if unable to connect to database successfully
* @link   http://www.php.net/manual/en/function.PDO-construct.php
*/
public static function connect($params)
{
	// Look for driver-specific subclass
	if (empty($params['driver']))
	{
		throw new InvalidArgumentException('No PDO driver specified for connection');
	}

	// Call method based on driver
	$class_name = __CLASS__ . '\\' . ucwords(strtolower($params['driver']));
	return call_user_func(array($class_name, __FUNCTION__), $params);
}

/*=======================================================*/
}
