<?php declare(strict_types=1);

namespace Yau\Cache;

use Yau\Cache\Adapter\AbstractAdapter;
use Psr\SimpleCache\CacheInterface;
use InvalidArgumentException;

/**
 * Cache that uses file system
 */
class FilesystemCache implements CacheInterface
{
/*=======================================================*/

/**
 * The params
 *
 * @var string
 */
private $params = [
	'depth'     => 5,      // Directory depth
	'encoding'  => 'json', // Type of encoding, either "json" or "serialize"
	'hash_algo' => 'md5',  // Hashing algorithm
];

/**
 * Constructor
 *
 * @param array $params
 * @throws InvalidArgumentException
 */
public function __construct(array $params)
{
	// Check parameters
	$params = $params + $this->params;

	$this->params = $params;
}

/**
 * Return the path to a cached item
 *
 * @param string $key
 * @return string
 */
protected function getKeyPath($key):string
{

}

/**
 * Fetch a value from the cache
 *
 * @param string $key
 * @param mixed $default
 */
public function get($key, $default = null)
{

}

/**
 * Store a value in the cache
 *
 * @param string $key
 * @param mixed $default
 * @param null|int|\DateInterval $ttl
 * @return bool
 */
public function set($key, $value, $ttl = null)
{
	$path = $this->getKeyPath($key);
	$handle = fopen
}

/**
 * Delete a value in the cache
 *
 * @param string $key
 * @return bool
 */
public function delete($key)
{
	$path = $this->getKeyPath($key);
}

/**
 * Clear the entire cache
 *
 * @return bool true on success and false on failure.
 */
public function clear()
{

}

/**
 * Return multiple cache items
 *
 * @param array $keys
 * @param mixed $default
 * @return iterable
 */
public function getMultiple($keys, $default = null)
{
	foreach ($keys as $key)
	{
		yield $this->get($key, $default);
	}

}

/**
 * Set multiple cache items
 *
 * @param iterable $values
 * @param null|int|\DateInterval $ttl
 * @return bool
 */
public function setMultiple($values, $ttl = null)
{
	$result = false;
	foreach ($values as $key => $value)
	{
		$result ||= $this->set($key, $value, $ttl);
	}
	return $result;
}

/**
 * Delete multiple cache items
 *
 * @param iterable $keys
 * @return bool
 */
public function deleteMultiple($keys)
{
	$result = false;
	foreach ($keys as $key)
	{
		$result ||= $this->delete($key);
	}
	return $result;
}

/**
 * Return whether an item exists in the cache or not
 *
 * @param string $key
 * @return bool
 */
public function has($key)
{
	$filename = $this->getKeyPath($key);
	return file_exists($filename);
}

/*=======================================================*/
}
