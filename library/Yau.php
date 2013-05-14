<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau
*/

namespace Yau;

// Define namespace separator
const NAMESPACE_SEPARATOR = '\\';

/**
*
* <code>
* require 'Yau/Yau.php';
* \Yau\Yau::registerAutoloader();
* </code>
*
* @author   John Yau
* @category Yau
* @package  Yau
*/
final class Yau
{
// Begin Yau
/*=======================================================*/

/**
* The base directory from which all modules are relative to
*
* @var string
*/
private static $basedir = null;

/**
* Set the base directory for framework files
*
* @var string
*/
public static function setBaseDir($path)
{
	self::$basedir = realpath($path);
}

/**
* Return whether a class is part of Yau Tools
*
* @param  string  $class_name
* @return boolean
*/
private function isYauClass($class_name)
{
	return (strcmp(array_shift(explode(NAMESPACE_SEPARATOR, $class_name)), __NAMESPACE__) == 0);
}

/**
* Load a Utility class
*
* @param string $class_name the name of the class to load
*/
public static function loadClass($class_name)
{
	if (!class_exists($class_name, FALSE)
		&& ($ns_pos = strpos($class_name, NAMESPACE_SEPARATOR)) !== FALSE
		&& strcmp(substr($class_name, 0, $ns_pos), __NAMESPACE__) == 0)
	{
//		echo "loading $class_name\n";
		$filename = __DIR__ . DIRECTORY_SEPARATOR . str_replace(NAMESPACE_SEPARATOR, DIRECTORY_SEPARATOR, substr($class_name, $ns_pos + 1)) . '.php';
		if (include($filename))
		{
			return TRUE;
		}
		throw new \Exception('Unable to load ' . $filename);
	}
	return FALSE;
}

/**
* Return whether a class or interface file exists to be loaded
*
* @param  string  $class_name
* @return boolean
*/
public static function classExists($class_name)
{
	return (class_exists($class_name, FALSE)
		|| (($ns_pos = strpos($class_name, NAMESPACE_SEPARATOR)) !== FALSE
			&& strcmp(substr($class_name, 0, $ns_pos), __NAMESPACE__) == 0
			&& ($filename = __DIR__ . DIRECTORY_SEPARATOR . str_replace(NAMESPACE_SEPARATOR, DIRECTORY_SEPARATOR, substr($class_name, $ns_pos + 1)) . '.php')
			&& is_readable($filename)));
}

/**
* Load a Utility interface
*
* @param string $interface_name the name of the interface to load
*/
public static function loadInterface($interface_name)
{
	if (!interface_exists($interface_name, FALSE))
	{
		require self::$basedir . DIRECTORY_SEPARATOR
			. str_replace(NAMESPACE_SEPARATOR, DIRECTORY_SEPARATOR, $interface_name) . '.php';
	}
}

/**
* Load a Utility function
*
* @param string $func the name of the function to load
*/
public static function loadFunction($func)
{
	if (!function_exists($func))
	{
		require self::$basedir . DIRECTORY_SEPARATOR
			. __CLASS__ . DIRECTORY_SEPARATOR
			. 'functions' . DIRECTORY_SEPARATOR . $func . '.php';
	}
}

/**
* Load a Utility file
*
* @param string $filename the name of the file to load
*/
public static function loadFile($filename)
{
	include self::$basedir . '/' . $filename;
}

/**
* Read a Utility file
*
* @param string $filename the name of the file to load
* @uses  readfile()
*/
public static function readFile($filename)
{
	readfile(self::$basedir . '/' . $filename);
}

/**
* Register autoload function using spl_autoload_register
*
* @see spl_autoload_register()
*/
public static function registerAutoloader()
{
	if (is_null(self::$basedir))
	{
		self::setBaseDir(dirname(__DIR__));
		spl_autoload_register(__CLASS__ . '::loadClass');
	}
}

/**
* Unregister the autoload function using spl_autoload_unregister
*
* @see spl_autoload_unregister()
*/
public static function unregisterAutoloader()
{
	if (!is_null(self::$basedir))
	{
		spl_autoload_unregister(__CLASS__ . '::loadClass');
	}
}

/*=======================================================*/
// End Yau
}
