<?php declare(strict_types=1);

namespace Yau\Cache\Adapter;

use Psr\SimpleCache\CacheInterface;
use InvalidArgumentException;
use DateInterval;

/**
 * Abstract adapter for caches
 */
abstract class AbstractAdapter implements CacheInterface
{
/*=======================================================*/

/**
 * Hash algorithm for keys
 *
 * @var array|bool
 */
protected $algo = 'md5';

/**
 * Encoding type for values
 *
 * @var string|bool
 */
protected $encoding = 'serialize';

/**
 * Constructor
 *
 * @param array $params
 * @throws InvalidArgumentException
 */
public function __construct(array $params = [])
{
	if (isset($params['algo']))
	{
		if (!empty($params['algo']) && !in_array($params['algo'], hash_algos()))
		{
			throw new InvalidArgumentException('Invalid hash algorithm ' . $params['algo']);
		}
		$this->algo = $params['algo'];
	}
	if (isset($params['encoding']))
	{
		if (!empty($params['encoding']) && !in_array($params['encoding'], ['json', 'serialize']))
		{
			throw new InvalidArgumentException('Invalid encoding ' . $params['encoding']);
		}
		$this->encoding = $params['encoding'];
	}
}

/**
 * Return hashed key
 *
 * @param string $key
 * @return string
 */
protected function hashKey($key):string
{
	return empty($this->algo) ? $key : hash($this->algo, $key);
}

/**
 * Return encoded value
 *
 * @param mixed $value
 * @return string
 */
protected function encodeValue($value):string
{
	switch ($this->encoding)
	{
		case 'json':
			return json_encode($value);
			break;
		case 'serialize':
			return serialize($value);
			break;
		default:
			return is_string($value) ? $value : serialize($value);
	}
}

/**
 * Return decoded value
 *
 * @param string $value
 * @return mixed
 */
protected function decodeValue(string $value)
{
	switch ($this->encoding)
	{
		case 'json':
			return json_decode($value, true);
			break;
		case 'serialize':
			return unserialize($value);
			break;
		default:
			return $value;
	}
}

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
