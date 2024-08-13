<?php declare(strict_types=1);

namespace Yau\Cache\Adapter;

use Yau\Cache\Adapter\AbstractAdapter;
use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use FilesystemIterator;

/**
 * Cache that uses file system
 */
class FilesystemAdapter extends AbstractAdapter
{
/*=======================================================*/

/**
 * The directory for cache files
 *
 * @var string
 */
private $path;

/**
 * Directory depth
 *
 * @var integer
 */
private $depth = 0;

/**
 * Directory permissions
 *
 * @var string
 */
private $perm = 0777;

/**
 * Constructor
 *
 * @param array $params
 * @throws InvalidArgumentException
 */
public function __construct(array $params = [])
{
	// Check parameters
	if (empty($params['path']))
	{
		throw new InvalidArgumentException('No path specified');
	}
	$params['path'] = rtrim($params['path'], DIRECTORY_SEPARATOR);
	if (is_dir($params['path']))
	{
		if (!is_writable($params['path']))
		{
			throw new InvalidArgumentException('Directory ' . $params['path'] . ' is not writable');
		}
	}
	elseif (!mkdir($params['path']))
	{
		throw new InvalidArgumentException('Missing path ' . $params['path']);
	}
	$this->path = $params['path'];
	if (isset($params['depth']))
	{
		$this->depth = intval($params['depth']);
	}
	if (!empty($params['perm']))
	{
		$this->perm = intval($params['perm']);
	}
	parent::__construct($params);
}

/**
 * Hash key
 *
 * @param string $key
 * @return string
 */
protected function hashKey($key):string
{
	return empty($this->algo)
		? preg_replace_callback('/\W/', fn($matches) => base_convert(ord($matches[0]), 10, 36), $key)
		: parent::hashKey($key);
}

/**
 * Return the filename to a cached item
 *
 * @param string $key
 * @return string|false
 * @throws InvalidArgumentException if key is zero length
 */
private function getKeyPath($key)
{
	$key = $this->hashKey($key);
	$key_length = strlen($key);
	if ($key_length == 0)
	{
		return false;
	}
	$path = $this->path;
	for ($i = 0; $i < $this->depth; $i++)
	{
		$path .= DIRECTORY_SEPARATOR . $key[$i % $key_length];
	}
	return $path . DIRECTORY_SEPARATOR . $key;
}

/**
 * Return decoded value
 *
 * @param string $value
 * @return mixed
 */
protected function decodeValue(string $value)
{
	return empty($this->encoding) ? unserialize($value) : parent::decodeValue($value);
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
	$is_hit = false;
	if (($path = $this->getKeyPath($key))
		&& file_exists($path)
		&& ($handle = fopen($path, 'r')))
	{
		if (flock($handle, LOCK_SH))
		{
			if (($contents = stream_get_contents($handle)) !== false
				&& ($item = $this->decodeValue($contents))
				&& is_array($item))
			{
				if (isset($item['ttl']) && $item['ttl'] < time())
				{
					unlink($path);
				}
				else
				{
					$value = $item['value'];
					$is_hit = true;
				}
			}
			flock($handle, LOCK_UN);
		}
		fclose($handle);
	}
	if ($is_hit)
	{
		return $value;
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
	$item = ['value'=>$value]
		+ (isset($ttl) ? ['ttl'=>$this->getTimestampForTTL($ttl)] : []);
	$encoded = $this->encodeValue($item);
	if (!is_string($encoded))
	{
		// Return false if unable to encode item
		return false;
	}
	$result = false;
	if (($path = $this->getKeyPath($key))
		&& ($directory = dirname($path))
		&& (is_dir($directory) || mkdir($directory, $this->perm, true))
		&& ($handle = fopen($path, 'c')))
	{
		if (flock($handle, LOCK_EX))
		{
			$result = ftruncate($handle, 0) && fwrite($handle, $encoded);
			flock($handle, LOCK_UN);
		}
		fclose($handle);
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
	return (($path = $this->getKeyPath($key)) && file_exists($path))
		? unlink($path) : true;
}

/**
 * Clear the entire cache
 *
 * @return bool true on success and false on failure.
 */
public function clear()
{
	$result = true;
	$directory = new RecursiveDirectoryIterator($this->path, FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::SKIP_DOTS);
	$iterator = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::CHILD_FIRST);
	$hash_length = empty($this->algo) ? '1,' : strval(strlen($this->hashKey('')));
	foreach ($iterator as $finfo)
	{
		$is_dir = $finfo->isDir();
		if (preg_match('/^\w{' . ($is_dir ? '1' : $hash_length) . '}$/', $finfo->getFilename()))
		{
			$func = $is_dir ? 'rmdir' : 'unlink';
			$result = $func($finfo->getPathname()) && $result;
		}
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
	$result = false;
	if (($path = $this->getKeyPath($key))
		&& file_exists($path)
		&& ($handle = fopen($path, 'r')))
	{
		if (flock($handle, LOCK_SH))
		{
			if (($contents = stream_get_contents($handle)) !== false
				&& ($item = $this->decodeValue($contents))
				&& is_array($item))
			{
				if (isset($item['ttl']) && intval($item['ttl']) < time())
				{
					unlink($path);
				}
				else
				{
					$result = true;
				}
			}
			flock($handle, LOCK_UN);
		}
		fclose($handle);
	}
	return $result;
}

/*=======================================================*/
}
