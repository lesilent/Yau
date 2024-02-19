<?php declare(strict_types = 1);

namespace Yau\Db\Connect;

use FilesystemIterator;
use CallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use InvalidArgumentException;

/**
 * Class for connecting to a database from the command line
 *
 * @author John Yau
 */
class Connect
{
/*=======================================================*/

/**
 * Return a list of available drivers
 *
 * @return array
 */
public static function getAvailableDrivers():array
{
	// Create iterator for main driver directories
	$driver_iterator = new FilesystemIterator(__DIR__ . DIRECTORY_SEPARATOR . 'Driver');
	$driver_iterator = new CallbackFilterIterator($driver_iterator, fn($current) => $current->isDir());

	// Iteratate over directories to find just files
	$drivers = [];
	foreach ($driver_iterator as $driver_dir)
	{
		$pathname = $driver_dir->getPathname();
		$pathlen = strlen($pathname);
		$iterator = new RecursiveDirectoryIterator($pathname);
		$iterator = new RecursiveIteratorIterator($iterator);
		$iterator = new CallbackFilterIterator($iterator, fn($current) => $current->isFile() && $current->getExtension() == 'php');
		foreach ($iterator as $filename)
		{
			$drivers[] = strtolower(basename(str_replace(DIRECTORY_SEPARATOR, '_', substr($filename->getPathname(), $pathlen + 1)), '.php'));
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
