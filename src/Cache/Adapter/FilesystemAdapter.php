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
 * Hashing algorithm for directories
 *
 * @var string
 */
private $hash = 'crc32b';

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
	if (!empty($params['hash']))
	{
		if (!in_array($params['hash'], hash_algos()))
		{
			throw new InvalidArgumentException('Invalid hash algorithm ' . $params['hash']);
		}
		$this->hash = $params['hash'];
	}
	if (isset($params['depth']))
	{
		$this->depth = intval($params['depth']);
	}
	if (!empty($params['perm']))
	{
		$this->perm = intval($params['perm']);
	}
}

/**
 * Return the filename to a cached item
 *
 * @param string $key
 * @return string
 */
private function getKeyPath($key):string
{
	$hash = hash($this->hash, $key);
	$path = $this->path;
	for ($i = 0; $i < $this->depth; $i++)
	{
		if (isset($hash[$i]))
		{
			$path .= DIRECTORY_SEPARATOR . $hash[$i];
		}
	}
	return $path . DIRECTORY_SEPARATOR . $hash;
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
				&& ($item = $this->decode($contents))
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
 * Encode a cached item
 *
 * @param array $item
 * @return string
 */
private function encode(array $item)
{
	return base64_encode(serialize($item));
}

/**
 * Decode a cached item
 *
 * @param string $item
 * @return array
 */
private function decode(string $item)
{
	return unserialize(base64_decode($item));
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
	$item = ['key'=>$key, 'value'=>$value]
		+ (isset($ttl) ? ['ttl'=>$this->getTimestampForTTL($ttl)] : []);
	if (($encoded = $this->encode($item)) === false)
	{
		// Return false if unable to encode item
		return false;
	}
	$path = $this->getKeyPath($key);
	$directory = dirname($path);
	$return = false;
	if (is_dir($directory) || mkdir($directory, $this->perm, true))
	{
		if ($handle = fopen($path, 'c'))
		{
			if (flock($handle, LOCK_EX))
			{
				$result = ftruncate($handle, 0) && fwrite($handle, $encoded);
				flock($handle, LOCK_UN);
			}
			fclose($handle);
		}
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
	$path = $this->getKeyPath($key);
	return (file_exists($path)) ? unlink($path) : true;
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
	$hash_length = strval(strlen(hash($this->hash, '')));
	foreach ($iterator as $finfo)
	{
		if (preg_match('/^[0-9a-f]{' . ($finfo->isDir() ? '1' : $hash_length) . '}$/', $finfo->getFilename()))
		{
			$result = unlink($finfo->getPathname()) && $result;
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
				&& ($item = $this->decode($contents))
				&& is_array($item))
			{
				if (isset($item['ttl']) && $item['ttl'] < time())
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
