<?php declare(strict_types=1);

namespace Yau\Cache\Adapter;

use Psr\SimpleCache\CacheInterface;
use Yau\Cache\Adapter\AbstractAdapter;
use InvalidArgumentException;

/**
 * Object to chain multiple cache adapters
 */
class ChainAdapter extends AbstractAdapter
{
/*=======================================================*/

/**
 * Array of adapters
 *
 * @var array
 */
private $adapters = [];

/**
 * Constructor
 *
 * @param array $params either an array of adapters or
 * @throws InvalidArgumentException
 */
public function __construct(array $params = [])
{
	$adapters = ($params['adapters'] ?? $params);
	foreach ($adapters as $adapter)
	{
		if (!($adapter instanceof CacheInterface))
		{
			throw new InvalidArgumentException('Invalid adapter passed');
		}
	}
	$this->adapters = $adapters;
}

/**
 * Fetch a value from the cache
 *
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
public function get($key, $default = null)
{
	foreach ($this->adapters as $adapter)
	{
		if (($value = $adapter->get($key)) !== null)
		{
			return $value;
		}
	}
	return ($default instanceof \Closure) ? call_user_func($default) : $default;
}

/**
 * Store a value in the cache
 *
 * @param string $key
 * @param mixed $value
 * @param null|int|\DateInterval $ttl
 * @return bool
 */
public function set($key, $value, $ttl = null)
{
	$result = true;
	foreach ($this->adapters as $adapter)
	{
		$result = $adapter->set($key, $value, $ttl) && $result;
	}
	return $result;
}

/**
 * Delete a value in the cache
 *
 * @param string $key
 * @return bool
 */
public function delete($key)
{
	$result = true;
	foreach ($this->adapters as $adapter)
	{
		$result = $adapter->delete($key) && $result;
	}
	return $result;
}

/**
 * Clear the entire cache
 *
 * @return bool true on success and false on failure.
 */
public function clear()
{
	$result = true;
	foreach ($this->adapters as $adapter)
	{
		$result = $adapter->clear() && $result;
	}
	return $result;
}

/**
 * Return whether an item exists in the cache or not
 *
 * @param string $key
 * @return bool
 */
public function has($key): bool
{
	foreach ($this->adapters as $adapter)
	{
		if ($adapter->has($key))
		{
			return true;
		}
	}
	return false;
}

/*=======================================================*/
}
