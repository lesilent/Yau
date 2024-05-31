<?php declare(strict_types = 1);

namespace Yau\Mutex;

use InvalidArgumentException;
use FilesystemIterator;

/**
* Mutex class
*
* @author John Yau
*/
class Mutex
{
/*=======================================================*/

/**
 * Factory method to return an instance of a mutext object
 *
 * @param  string $type     the type of mutex
 * @param  mixed  $resource optional resource
 * @param  array  $options  associative array of options
 * @return object
 * @throws Exception if invalid type
 */
public static function factory($type, $resource = null, $options = [])
{
	if (!(preg_match('/^\w+$/', $type) && file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'Adapter' . DIRECTORY_SEPARATOR . ucfirst($type) . '.php')))
	{
		throw new InvalidArgumentException('Invalid type');
	}
	$class_name = __NAMESPACE__ . '\\Adapter\\' . ucfirst($type);
	return new $class_name($resource, $options);
}

/**
 * Return an array of available adapters
 *
 * @return array
 */
public static function getAvailableAdapters():array
{
	// Iteratate over directory to find files
	$adapters = [];
	$iterator = new FilesystemIterator(__DIR__ . DIRECTORY_SEPARATOR . 'Adapter', FilesystemIterator::KEY_AS_FILENAME);
	foreach ($iterator as $filename => $finfo)
	{
		if (preg_match('/^\w+\.php$/', $filename) && $finfo->isFile())
		{
			$adapters[] = strtolower($finfo->getBasename('.php'));
		}
	}
	sort($adapters);

	// Return adapters
	return $adapters;
}

/*=======================================================*/
}
