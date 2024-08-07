<?php declare(strict_types=1);

namespace Yau\Cache\Adapter;

use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use DateInterval;

/**
 * Abstract adapter for caches
 */
abstract class AbstractAdapter implements CacheInterface
{
/*=======================================================*/

/**
 * Parameters
 *
 * @var array
 */
protected $params = [];

/**
 * Constructor
 *
 * @param array $params
 * @throws InvalidArgumentException
 */
abstract function __construct(array $params = []);

/**
 * Return the timestamp for TTL
 *
 * @param null|int|DateInterval $ttl
 * @return null|int
 */
protected function getTimestampForTTL($ttl)
{
	if (isset($ttl))
	{
		return ($ttl instanceof DateInterval)
			? date_create()->add($ttl)->getTimestamp()
			: time() + $ttl;
	}
	return null;
}

/**
 * Fetch a value from the cache
 *
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
abstract public function get($key, $default = null);

/**
 * Store a value in the cache
 *
 * @param string $key
 * @param mixed $default
 * @param null|int|DateInterval $ttl
 * @return bool
 */
abstract public function set($key, $value, $ttl = null);

/**
 * Delete a value in the cache
 *
 * @param string $key
 * @return bool
 */
abstract public function delete($key);

/**
 * Clear the entire cache
 *
 * @return bool true on success and false on failure.
 */
abstract public function clear();

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
		$result = $this->set($key, $value, $ttl) || $result;
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
		$result = $this->delete($key) || $result;
	}
	return $result;
}

/**
 * Return whether an item exists in the cache or not
 *
 * @param string $key
 * @return bool
 */
abstract public function has($key);

/*=======================================================*/
}
