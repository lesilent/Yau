<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Yau\Mutex\Mutex;
use Yau\Mutex\Adapter\File;

/**
 * Tests for Yau\Mutex\Mutex
 */
class MutexTest extends TestCase
{
/*=======================================================*/

/**
 * Number of microseconds to sleep
 *
 * @var integer
 */
private static $SLEEP_TIME = 10000;

/**
 */
public function testGetAvailableAdapters():void
{
	$adapters = Mutex::getAvailableAdapters();
	$this->assertIsArray($adapters);
	$this->assertContains('file', $adapters);
}

/**
 */
public function testFactoryException():void
{
	$this->expectException(InvalidArgumentException::class);
	Mutex::factory('!Invalid!');
}

/**
 */
public function testFactory():void
{
	$filename = tempnam(sys_get_temp_dir(), 'mutex');
	$mutex = Mutex::factory('file', $filename);
	$this->assertInstanceOf(File::class, $mutex);
}

/**
 */
public function checkMutex($mutex):void
{
	$pid = pcntl_fork();
	if ($pid == -1)
	{
		throw new Exception('Unable to fork');
	}
	elseif ($pid)
	{
		// Parent
		$this->assertTrue($mutex->acquire());
		usleep(self::$SLEEP_TIME);
		$this->assertTrue($mutex->release());
		usleep(self::$SLEEP_TIME * 2);
		$this->assertFalse($mutex->acquire());
		usleep(self::$SLEEP_TIME * 2);
		pcntl_wait($status);
		$this->assertTrue($mutex->acquire());
	}
	else
	{
		// Child
		$this->assertFalse($mutex->acquire());
		usleep(self::$SLEEP_TIME * 2);
		$this->assertTrue($mutex->acquire());
		usleep(self::$SLEEP_TIME * 2);
//		$this->assertTrue($mutex->release()); // Optional since child terminating should have the same effect
		exit;
	}
}

/**
 */
public function testFile():void
{
	$filename = tempnam(sys_get_temp_dir(), 'mutex');
	$mutex = Mutex::factory('file', $filename);
	$this->checkMutex($mutex);
	unlink($filename);
}

/**
 */
public function testSemaphore():void
{
	$semaphore = sem_get(ftok(__FILE__, 'm'));
	$mutex = Mutex::factory('semaphore', $semaphore);
	$this->checkMutex($mutex);
	sem_remove($semaphore);
}

/*=======================================================*/
}
