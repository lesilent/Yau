<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
*/

namespace Yau\Db\Connect\Driver\Pear;

use Yau\Db\Connect\Driver\DriverInterface;
use Yau\Db\Connect\Driver\Pear\Pear;
use Yau\Db\Connect\Exception\ConnectException;

/**
* Parent class for connecting to a database using PEAR classes
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
* @link     http://pear.php.net/manual/en/core.pear.pear.php
*/
class Pear implements DriverInterface
{
/*=======================================================*/

/**
* The cached path to PEAR classes
*
* @var string
*/
private static $path;

/**
* Return the path to PEAR libraries
*
* @return string the path to the PEAR libraries, or FALSE if unable to
*                locate them
*/
public static function getPath()
{
	if (empty(self::$path))
	{
		foreach (explode(PATH_SEPARATOR, get_include_path()) as $path)
		{
			$filename = $path . DIRECTORY_SEPARATOR . 'PEAR.php';
			if (file_exists($filename) && strcmp($path, __DIR__) != 0)
			{
				return self::$path = $path;
			}
		}
	}
	return FALSE;
}

/**
*
* @param  array $params
* @return mixed
*/
public static function connect($params)
{
	$class_name = __CLASS__ . '\\' . ucwords($params['driver']);
	return call_user_func(array($class_name, __FUNCTION__), $params);
}

/*=======================================================*/
}
