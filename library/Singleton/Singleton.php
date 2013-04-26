<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_Singleton
*/

namespace Yau\Singleton;

/**
* A class to help singleton design pattern implementation
*
* With the introduction of late static binding in PHP version 5.3.0, a class
* can implement the singleton design pattern by simply extending this class.
*
* Implementing a singleton:
* <code>
* use Yau\Singleton\Singleton;
*
* // PHP 5.3.0+ implementation:
* class MySingleton extends Singleton
* {
* }
* </code>
*
* PHP does not supporting multiple inheritance. So, if a class is already
* extending some other class, then it's not possible to also extend this
* class. To get around this
* .
* Work around for multiple inheritance:
* <code>
* class MyChildSingleton extends SomeParentClass
* {
*     // PHP 5.3.0+ implementation
*     public static function getInstance()
*     {
*         return Singleton::getInstance(get_called_class());
*     }
* }
* </code>
*
* @author   John Yau
* @category Yau
* @package  Yau_Singleton
*/
class Singleton
{
/*=======================================================*/

/**
* Instances of objects
*
* @var array
*/
private static $instances = array();

/**
* Return an instance of object
*
* @param  string $name the name of the class to return an instance of;
*                      required for PHP versions older than 5.3.0
* @return object
* @throws Exception if name is not a valid class
*/
public static function getInstance($name = NULL)
{
	// Get name of class to instantiate
	if (empty($name))
	{
		$name = get_called_class();
	}
	elseif (!class_exists($name, FALSE))
	{
		throw new Exception('Unknown class ' . $name);
	}

	// Instantiate object
	if (empty(self::$instances[$name]))
	{
		self::$instances[$name] = new $name();
	}

	// Return instance
	return self::$instances[$name];
}

/*=======================================================*/
}
