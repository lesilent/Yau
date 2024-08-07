<?php declare(strict_types=1);

namespace Yau\Cache\Adapter;

use Yau\Cache\Adapter\AbstractAdapter;
use InvalidArgumentException;

/**
 * Cache that uses file system
 */
class ArrayAdapter extends AbstractAdapter
{
/*=======================================================*/

/**
 * Array to store cache items
 *
 * @var string
 */
private $items = [];

/**
 * Constructor
 *
 * @param array $params
 */
public function __construct(array $params = [])
{
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
	if (isset($this->items[$key]))
	{
		if (isset($this->items[$key]['ttl']) && $this->items[$key]['ttl'] < time())
		{
			unset($this->items[$key]);
		}
		else
		{
			return $this->items[$key]['value'];
		}
	}
	return ($default instanceof \Closure) ? call_user_func($default) : $default;
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
	$this->items[$key] = ['key'=>$key, 'value'=>$value]
		+ (isset($ttl) ? ['ttl'=>$this->getTimestampForTTL($ttl)] : []);
	return true;
}

/**
 * Delete a value in the cache
 *
 * @param string $key
 * @return bool
 */
public function delete($key)
{
	unset($this->items[$key]);
	return true;
}

/**
 * Clear the entire cache
 *
 * @return bool true on success and false on failure.
 */
public function clear()
{
	$this->items = [];
	return true;
}

/**
 * Return whether an item exists in the cache or not
 *
 * @param string $key
 * @return bool
 */
public function has($key)
{
	if (isset($this->items[$key]))
	{
		if (isset($this->items[$key]['ttl']) && $this->items[$key]['ttl'] < time())
		{
			unset($this->items[$key]);
		}
		else
		{
			return true;
		}
	}
	return false;
}

/*=======================================================*/
}
