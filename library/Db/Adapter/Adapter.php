<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
*/

namespace Yau\Db\Adapter;

/**
* A database wrapper object for interacting with databases
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
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
* @return string the database driver name, or FALSE if unable to determine
*                driver
*/
public static function getDriver($dbh)
{
	if (is_object($dbh))
	{
		$class_name = get_class($dbh);
		if ($class_name == 'PDO')
		{
			/**
			* PDO driver
			*
			* @link http://www.php.net/manual/en/ref.pdo.php
			*/
			return 'pdo_' . strtolower($dbh->getAttribute(\PDO::ATTR_DRIVER_NAME));
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
		elseif ($class_name == 'mysqli')
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
		preg_match('/^(\w+)\slink/', $type, $match);

		switch ($type)
		{
		case 'mysql link':
		case 'mysql link persistent':
			/**
			* MySQL
			*
			* @link http://www.php.net/manual/en/ref.mysql.php
			*/
			return strtolower($match[1]);
			break;
		case 'odbc link':
		case 'odbc link persistent':
			/**
			* ODBC
			*
			* @link http://www.php.net/manual/en/ref.uodbc.php
			*/
			return strtolower($match[1]);
			break;
		case 'pgsql link':
		case 'pgsql link persistent':
			/**
			* PostgreSQL
			*
			* @link http://www.php.net/manual/en/ref.pgsql.php
			*/
			return strtolower($match[1]);
			break;
		}
	}

	// Return FALSE if unable to determine driver
	return FALSE;
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
	if (is_object($dbh) && $dbh instanceof Yau\Db\Adapter\Driver\AbstractDriver)
	{
		return $dbh;
	}

	// Figure out driver for database object or resource
	$driver = self::getDriver($dbh);
	if (empty($driver))
	{
		$dbh_name = (is_object($dbh)) ? get_class($dbh) : gettype($dbh);
		throw new Exception('Unable to find driver for ' . $dbh_name);
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

/*=======================================================*/
}
