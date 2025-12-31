<?php declare(strict_types=1);

namespace Yau\Cache;

use PHPUnit\Framework\TestCase;
use Yau\Cache\Adapter\ArrayAdapter;
use Yau\Cache\Adapter\AbstractAdapter;
use Yau\Cache\Adapter\ChainAdapter;
use Yau\Cache\Adapter\DbAdapter;
use Yau\Cache\Adapter\FilesystemAdapter;
use Yau\Db\Adapter\Adapter;
use Yau\Db\Connect\Connect;

/**
 */
class AdapterTest extends TestCase
{
/*=======================================================*/

/**
 * @return iterable
 */
public function adapterProvider(): iterable
{
	foreach (['crc32b', 'md5', 'sha1', false] as $algo)
	{
		foreach (['json', 'serialize', false] as $encoding)
		{
			$params = ['algo'=>$algo, 'encoding'=>$encoding];
			yield ['array', $params];
			$path = tempnam(sys_get_temp_dir(), 'cache');
			unlink($path);
			yield ['filesystem', $params + ['path'=>$path]];
		}
	}
}

/**
 * Providers to clear cached items
 *
 * @param object $adapter
 * @param string $key
 * @return iterable
 */
private function clearGenerator($adapter, $key): iterable
{
	yield fn() => $adapter->delete($key);
	yield fn() => $adapter->clear();
	yield fn() => sleep(2);
}

/**
 * @param string $driver
 * @param array $params
 * @dataProvider adapterProvider
 */
public function testAdapter($driver, $params): void
{
	$class_name = '\\Yau\\Cache\\Adapter\\' . ucfirst($driver) . 'Adapter';
	$adapter = new $class_name($params);
	$this->assertInstanceOf(AbstractAdapter::class, $adapter);

	// Create key and values to test with
	$key = uniqid('test', true);
	$scalar_value = random_bytes(mt_rand(32, 1024));
	$test_values = [random_bytes(mt_rand(32, 1024))];
	if (!empty($params['encoding']))
	{
		// For json, we remove characters that cause encoding issues
		if ($params['encoding'] == 'json')
		{
			$test_values[0] = json_encode($test_values[0], JSON_INVALID_UTF8_IGNORE);
		}

		// If there's encoding, then also test with a non-scalar value
		$test_values[] = [$key=>$test_values];
	}

	// Test the cache with the values
	foreach ($test_values as $value)
	{
		foreach ($this->clearGenerator($adapter, $key) as $func)
		{
			// Store value in cache
			$this->assertFalse($adapter->has($key));
			$this->assertTrue($adapter->set($key, $value, 1));
			$this->assertTrue($adapter->has($key));
			$this->assertSame($value, $adapter->get($key));

			// Clear value and make sure it's not in cache
			call_user_func($func);
			$this->assertFalse($adapter->has($key));
			$this->assertNull($adapter->get($key));
			$default = random_bytes(mt_rand(32, 1024));
			$this->assertSame($default, $adapter->get($key, $default));
			$default = random_bytes(mt_rand(32, 1024));
			$this->assertSame($default, $adapter->get($key, fn() => $default));

			// Store values with various TTL
			$this->assertTrue($adapter->set($key, $value, -1));
			$this->assertNull($adapter->get($key));
			$this->assertFalse($adapter->has($key));
			$this->assertTrue($adapter->set($key, $value, new \DateInterval('P1D')));
			$this->assertTrue($adapter->has($key));
			$this->assertTrue($adapter->clear());
			$this->assertFalse($adapter->has($key));
			$this->assertNull($adapter->get($key));
		}
	}
}

/**
 * Test chain adapter
 */
public function testChainAdapter(): void
{
	// Initiate adapters
	$arr = new ArrayAdapter();
	$path = tempnam(sys_get_temp_dir(), 'cache');
	unlink($path);
	$fs = new FilesystemAdapter(['path'=>$path]);
	$adapters = [$arr, $fs];
	$last_offset = count($adapters) - 1;
	$chain = new ChainAdapter($adapters);

	// Set values
	$key = uniqid('test', true);
	$value = random_bytes(mt_rand(32, 1024));
	$this->assertTrue($chain->set($key, $value));
	$this->assertTrue($chain->has($key));

	// Test cascading of adapters
	foreach ($adapters as $offset => $adapter)
	{
		$this->assertTrue($adapter->has($key));
		$this->assertSame($value, $chain->get($key));
		$this->assertTrue($adapter->delete($key));
		$this->assertFalse($adapter->has($key));
		$cached_value = $chain->get($key);
		if ($offset < $last_offset)
		{
			$this->assertSame($value, $cached_value);
			$this->assertTrue($chain->has($key));
		}
		else
		{
			$this->assertNull($cached_value);
			$this->assertFalse($chain->has($key));
		}
	}

	// Test deleting key from chain
	$key = uniqid('test', true);
	$value = random_bytes(mt_rand(32, 1024));
	$this->assertTrue($chain->set($key, $value));
	$this->assertTrue($chain->has($key));
	$this->assertSame($value, $chain->get($key));
	foreach ($adapters as $adapter)
	{
		$this->assertTrue($adapter->has($key));
		$this->assertSame($value, $adapter->get($key));
	}
	;
	$this->assertTrue($chain->delete($key));
	$this->assertFalse($chain->has($key));
	$this->assertNull($chain->get($key));
	foreach ($adapters as $adapter)
	{
		$this->assertFalse($adapter->has($key));
		$this->assertNull($adapter->get($key));
	}
}

/**
 * @return iterable
 */
public function connectProvider(): iterable
{
	$i = 1;
	do
	{
		if (empty($_ENV["DB_DRIVER_$i"]))
		{
			if ($i < 2)
			{
				$this->markTestSkipped('No db credentials defined in phpunit.xml');
			}
			return;
		}
		$params = [];
		foreach (['DRIVER', 'HOST', 'DBNAME', 'USERNAME', 'PASSWORD'] as $field)
		{
			$params[strtolower($field)] = $_ENV["DB_{$field}_{$i}"] ?? null;
		}
		// Return the db parameters
		yield [$params];
		$i++;
	}
	while (true);
}

/**
 * Test database adapter
 *
 * @param array $params
 * @dataProvider connectProvider
 */
public function testDbAdapter($params): void
{
	$dbh = Connect::factory('PDO', $params);
	$this->assertNotFalse($dbh);
	if (is_object($dbh) || is_resource($dbh))
	{
		$dbh = Adapter::factory($dbh);

		// Test with db handler, then with closure that returns db handler
		foreach ([$dbh, fn() => $dbh] as $db)
		{
			$this->testAdapter('db', ['dbh'=>$db]);
		}
	}
}

/**
 * Test filesystem adapter
 */
public function testFilesystemAdapter(): void
{
	$path = tempnam(sys_get_temp_dir(), 'cache');
	unlink($path);

	// Test with no depth
	$fcount = mt_rand(10, 20);
	$adapter = new FilesystemAdapter(['path'=>$path]);
	$iterator = new \FilesystemIterator($path);
	for ($i = 0; $i < $fcount; $i++)
	{
		$filecount = 0;
		foreach ($iterator as $finfo)
		{
			$this->assertTrue($finfo->isFile());
			$filecount++;
		}
		$this->assertSame($i, $filecount);
		$key = uniqid('test', true);
		$value = random_bytes(mt_rand(32, 1024));
		$this->assertFalse($adapter->has($key));
		$this->assertTrue($adapter->set($key, $value));
		$this->assertTrue($adapter->has($key));
	}
	$adapter->clear();
	$filecount = 0;
	foreach ($iterator as $finfo)
	{
		$filecount++;
	}
	$this->assertSame(0, $filecount);
	$this->assertTrue(rmdir($path));

	// Test with depth
	$fcount = mt_rand(10, 20);
	$adapter = new FilesystemAdapter(['path'=>$path, 'depth'=>mt_rand(2, 10)]);
	for ($i = 0; $i < $fcount; $i++)
	{
		foreach ($iterator as $finfo)
		{
			$this->assertTrue($finfo->isDir());
		}
		$key = uniqid('test', true);
		$value = random_bytes(mt_rand(32, 1024));
		$this->assertFalse($adapter->has($key));
		$this->assertTrue($adapter->set($key, $value));
		$this->assertTrue($adapter->has($key));
	}
	$directory = new \RecursiveDirectoryIterator($path, \FilesystemIterator::CURRENT_AS_FILEINFO | \FilesystemIterator::SKIP_DOTS);
	$iterator = new \RecursiveIteratorIterator($directory, \RecursiveIteratorIterator::LEAVES_ONLY);
	$filecount = 0;
	foreach ($iterator as $finfo)
	{
		$this->assertTrue($finfo->isFile());
		$filecount++;
	}
	$this->assertEquals($fcount, $filecount);
	$adapter->clear();
	$filecount = 0;
	foreach ($iterator as $finfo)
	{
		$filecount++;
	}
	$this->assertSame(0, $filecount);
}

/*=======================================================*/
}


