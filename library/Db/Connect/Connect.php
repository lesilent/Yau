<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
*/

namespace Yau\Db\Connect;

use Yau\Db\Connect\Exception\InvalidArgumentException;

/**
* Class for connecting to a database from the command line
*
* @category Yau
* @package  Yau_Db
*/
class Connect
{
/*=======================================================*/

/**
* Return a list of available drivers
*
* @return array
*/
public static function getAvailableDrivers()
{
	// Create iterator for main driver directories
	$callback = function ($current, $key, $iterator)
	{
		return $current->isDir();
	};
	$driver_iterator = new \FilesystemIterator(__DIR__ . DIRECTORY_SEPARATOR . 'Driver');
	$driver_iterator = new \CallbackFilterIterator($driver_iterator, $callback);

	// Iteratate over directories to find just files
	$callback = function ($current, $key, $iterator)
	{
		return $current->isFile();
	};
	$drivers = array();
	foreach ($driver_iterator as $driver_dir)
	{
		$pathlen = strlen($driver_dir);
		$iterator = new \RecursiveDirectoryIterator($driver_dir);
		$iterator = new \RecursiveIteratorIterator($iterator);
		$iterator = new \CallbackFilterIterator($iterator, $callback);
		foreach ($iterator as $filename)
		{
			$drivers[] = strtolower(basename(str_replace(DIRECTORY_SEPARATOR, '_', substr($filename, $pathlen + 1)), '.php'));
		}
	}

	// Return drivers
	return $drivers;
}

/**
* Connect to database
*
* @param string $driver
* @param array  $params
*/
public static function factory($driver, array $params)
{
	// Check driver name
	if (!preg_match('/^\w+$/', $driver))
	{
		throw new InvalidArgumentException('Invalid driver ' . $driver);
	}

	// Adjust capitalization for class
	$driver = str_replace(' ', '_', ucwords(str_replace('_', ' ', strtolower($driver))));

	// Check whether driver exists
	$driver_dir = (($spos = strpos($driver, '_')) === FALSE)
		? $driver
		: substr($driver, 0, $spos);
	$filename = __DIR__ . DIRECTORY_SEPARATOR . 'Driver'
		. DIRECTORY_SEPARATOR . $driver_dir . DIRECTORY_SEPARATOR
		. str_replace('_', DIRECTORY_SEPARATOR, $driver) . '.php';
	if (!file_exists($filename))
	{
		throw new InvalidArgumentException('Unsupported driver ' . $driver);
	}

	// Connect using driver
	$class_name = __NAMESPACE__ . '\\Driver\\' . $driver_dir . '\\' . str_replace('_', '\\', $driver);
	return call_user_func(array($class_name, 'connect'), $params);
}

/*=======================================================*/
}
