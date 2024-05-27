<?php declare(strict_types = 1);

namespace Yau\Db\Adapter;

use PDO;
use CallbackFilterIterator;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use UnexpectedValueException;

/**
 * A database wrapper object for interacting with databases
 *
 * @author John Yau
 */
abstract class Adapter
{
/*=======================================================*/

/**
 * Return the database driver for a database handler object or resource
 *
 * Note: this function isn't designed for general public usage, and used
 * by Yau\Db classes
 *
 * Current drivers:
 * <ul>
 * <li>MYSQL
 * <li>MYSQLI
 * <li>PDO_MYSQL
 * <li>PDO_ODBC
 * <li>PEAR_DB_MYSQL
 * </ul>
 *
 * @param  mixed  $dbh either a database object or resource
 * @return string the database driver name, or false if unable to determine
 *                driver
 */
public static function getDriver($dbh)
{
	if (is_object($dbh))
	{
		$class_name = get_class($dbh);
		if (strcmp($class_name, 'PDO') == 0)
		{
			/**
			* PDO driver
			*
			* @link http://www.php.net/manual/en/ref.pdo.php
			*/
			return 'pdo_' . strtolower($dbh->getAttribute(PDO::ATTR_DRIVER_NAME));
		}
		elseif (preg_match('/^DB_(\w+)$/', $class_name, $match))
		{
			/**
			* PEAR DB
			*
			* @link http://pear.php.net/manual/en/package.database.db.php
			*/
			return 'pear_db_' . strtolower($match[1]);
		}
		elseif (strcmp($class_name, 'mysqli') == 0)
		{
			/**
			* MySQL Improved Extension
			*
			* @link http://www.php.net/manual/en/ref.mysqli.php
			*/
			return 'mysqli';
		}
		/*
		elseif (preg_match('/^MDB2_Driver_(\w+)$/', $class_name, $match))
		{
			* PEAR MDB2
			*
			* @link http://pear.php.net/manual/en/package.database.mdb2.php
			return 'Pear_Mdb2_' . ucwords(strtolower($match[1]));
		}
		*/
	}
	elseif (is_resource($dbh))
	{
		$type = get_resource_type($dbh);
		preg_match('/^(\w+)\slink/', $type, $matches);

		switch ($type)
		{
		case 'mysql link':
		case 'mysql link persistent':
			/**
			* MySQL
			*
			* @link http://www.php.net/manual/en/ref.mysql.php
			*/
			return strtolower($matches[1]);
			break;
		case 'odbc link':
		case 'odbc link persistent':
			/**
			* ODBC
			*
			* @link http://www.php.net/manual/en/ref.uodbc.php
			*/
			return strtolower($matches[1]);
			break;
		case 'pgsql link':
		case 'pgsql link persistent':
			/**
			* PostgreSQL
			*
			* @link http://www.php.net/manual/en/ref.pgsql.php
			*/
			return strtolower($matches[1]);
			break;
		}
	}

	// Return false if unable to determine driver
	return false;
}

/**
* Return an instance of object using the factory design pattern
*
* @param  mixed  $dbh a database handler object or resource
* @return object a Adapter object
* @throws Exception if invalid driver or unable to find driver to use for
*                   database handler object or resource
*/
public static function factory($dbh)
{
	// Return back object if already a Adapter object
	if (is_object($dbh) && $dbh instanceof \Yau\Db\Adapter\Driver\AbstractDriver)
	{
		return $dbh;
	}

	// Figure out driver for database object or resource
	$driver = self::getDriver($dbh);
	if (empty($driver))
	{
		$dbh_name = (is_object($dbh)) ? get_class($dbh) : gettype($dbh);
		throw new UnexpectedValueException('Unable to find driver for ' . $dbh_name);
	}
	$driver_dir = (($spos = strpos($driver, '_')) === FALSE)
		? $driver
		: substr($driver, 0, $spos);

	// Call constructor
	$class_name = __NAMESPACE__ . '\\Driver\\' . ucfirst($driver_dir) . '\\'
		. str_replace(' ', '\\', ucwords(str_replace('_', ' ', $driver)));
	return new $class_name($dbh);
}

/**
* Return a list of available drivers
*
* @return array
*/
public static function getAvailableDrivers()
{
	// Create iterator for main driver directories
	$driver_iterator = new CallbackFilterIterator(new FilesystemIterator(__DIR__ . DIRECTORY_SEPARATOR . 'Driver'), fn($current) => $current->isDir());

	// Iteratate over directories to find just files
	$drivers = [];
	foreach ($driver_iterator as $driver_dir)
	{
		$path = $driver_dir->getPathname();
		$pathlen = strlen($path);
		$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
		$iterator = new CallbackFilterIterator($iterator, fn($current) => $current->isFile());
		foreach ($iterator as $finfo)
		{
			$drivers[] = strtolower(basename(str_replace(DIRECTORY_SEPARATOR, '_', substr($finfo->getPathname(), $pathlen + 1)), '.php'));
		}
	}

	// Return drivers
	return $drivers;
}

/*=======================================================*/
}
