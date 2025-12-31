<?php declare(strict_types = 1);

namespace Yau\Singleton;

use InvalidArgumentException;

/**
 * A class to help singleton design pattern implementation
 *
 * Implementing a singleton:
 * <code>
 * use Yau\Singleton\Singleton;
*
 * // Implementation:
 * class MySingleton extends Singleton
 * {
 * }
 * </code>
 *
 * Work around for multiple inheritance:
 * <code>
 * class MyChildSingleton extends SomeParentClass
 * {
 *     public static function getInstance()
 *     {
 *         return Singleton::getInstance(get_called_class());
 *     }
 * }
 * </code>
 *
 * @author John Yau
 */
class Singleton
{
/*=======================================================*/

/**
 * Instances of objects
 *
 * @var array
 */
private static $instances = [];

/**
 * Return an instance of object
 *
 * @param string $name the name of the class to return an instance of;
 *                      required for PHP versions older than 5.3.0
 * @return object
 * @throws InvalidArgumentException if name is not a valid class
 */
public static function getInstance(?string $name = null)
{
	// Get name of class to instantiate
	if (empty($name))
	{
		$name = get_called_class();
	}
	elseif (!class_exists($name, false))
	{
		throw new InvalidArgumentException('Unknown class ' . $name);
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
