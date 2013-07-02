<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau
*/

namespace Yau;

use Yau\ClassLoader\ClassLoader;

/**
* Main Yau class
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
* The class loader object
*
* @var object
*/
private static $loader;

/**
* Return an instance of the class loader
*
* @return object
*/
private static function getLoader()
{
	if (empty(self::$loader))
	{
		require __DIR__ . DIRECTORY_SEPARATOR . 'ClassLoader' . DIRECTORY_SEPARATOR . 'ClassLoader.php';
		self::$loader = new ClassLoader();
		self::$loader->registerNamespace(__NAMESPACE__, __DIR__);
	}
	return self::$loader;
}

/**
* Return whether a class belongs to the current namespace or not
*
* @param  string  $class_name
* @return boolean
*/
private static function isYauClass($class_name)
{
	return (($ns_pos = strpos($class_name, '\\')) !== FALSE
		&& strcmp(substr($class_name, 0, $ns_pos), __NAMESPACE__) == 0);
}

/**
* Return whether a file for a class exists or not
*
* @param  string  $class_name the name of the class
* @return boolean
*/
public static function classExists($class_name)
{
	if (self::isYauClass($class_name))
	{
		$filename = self::getLoader()->getPath($class_name);
		return file_exists($filename);
	}
	return FALSE;
}

/**
* Load a class
*
* @param string $class_name the name of the class to load
*/
public static function loadClass($class_name)
{
	if (!class_exists($class_name, FALSE) && self::isYauClass($class_name))
	{
		$filename = self::getLoader()->getPath($class_name);
		if (include($filename))
		{
			return TRUE;
		}
		throw new \Exception('Unable to load ' . $filename);
	}
	return FALSE;
}

/**
* Load an interface
*
* @param string $interface_name the name of the interface to load
*/
public static function loadInterface($interface_name)
{
	if (!interface_exists($interface_name, FALSE) && self::isYauClass($class_name))
	{
		$filename = self::getLoader()->getPath($interface_name);
		if (include($filename))
		{
			return TRUE;
		}
		throw new \Exception('Unable to load ' . $filename);
	}
}

/**
* Register autoload function
*
* @return boolean
*/
public static function registerAutoloader()
{
	return self::getLoader()->register();
}

/**
* Unregister the autoload function using spl_autoload_unregister
*
* @return boolean
*/
public static function unregisterAutoloader()
{
	if (!empty(self::$loader))
	{
		return self::getLoader()->unregister();
	}
}

/*=======================================================*/
// End Yau
}
