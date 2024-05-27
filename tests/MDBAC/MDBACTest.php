<?php declare(strict_types = 1);

use PHPUnit\Framework\TestCase;
use Yau\MDBAC\MDBAC;

/**
 * Tests for Yau\MDBAC\MDBAC
 */
class MDBACTest extends TestCase
{
/*=======================================================*/

/**
 * The database used for testing
 *
 * @var string
 */
private static $TEST_DB = 'projectx';

/**
 * The MDBAC object
 *
 * @var object
 */
protected $mdbac;

/**
 */
public function setUp():void
{
	$this->mdbac = new MDBAC(__DIR__ . DIRECTORY_SEPARATOR . 'db.conf.xml');
}

/**
 * @return array
 */
public function providerDriverConstants():array
{
	// Get path to the MDBAC Connect classes
	$reflect = new ReflectionClass('Yau\Db\Connect\Connect');
	$path = dirname($reflect->getFileName()) . DIRECTORY_SEPARATOR . 'Driver';
	$pathlen = strlen($path);

	// Iterate over the files to get drivers
	$drivers = [];
	$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::CURRENT_AS_PATHNAME | FilesystemIterator::SKIP_DOTS));
	foreach ($iterator as $pathname)
	{
		if (is_file($pathname)
			&& strcmp(pathinfo($pathname, PATHINFO_EXTENSION), 'php') == 0
			&& ($dirpos = strpos($pathname, DIRECTORY_SEPARATOR, $pathlen + 1)) !== false
			&& ($constname = basename(str_replace(DIRECTORY_SEPARATOR, '_', substr($pathname, $dirpos + 1)), '.php')))
		{
			$drivers[] = [strtoupper($constname)];
		}
	}

	// Return drivers
	return $drivers;
}

/**
 * @dataProvider providerDriverConstants
 */
public function testDriverConstants($constname):void
{
	$this->assertTrue(defined('Yau\MDBAC\MDBAC::' . $constname));
}

/**
 */
public function testGetConfig():void
{
	$this->assertInstanceOf('Yau\MDBAC\Config', $this->mdbac->getConfig());
}

/**
 */
public function testConnect():void
{
	$this->assertIsString($this->mdbac->connect('CLI', self::$TEST_DB));
	$this->assertIsString($this->mdbac->connectOnce('CLI', self::$TEST_DB));
	foreach ($this->providerDriverConstants() as $driver)
	{
		$driver = reset($driver);
		if (preg_match('/^CLI/i', $driver))
		{
			$this->assertIsString($this->mdbac->connect($driver, self::$TEST_DB));
			$this->assertIsString($this->mdbac->connectOnce($driver, self::$TEST_DB));
		}
	}
}

/**
 */
public function testInvalidDriverException():void
{
	$this->expectException(InvalidArgumentException::class);
	$dbh = $this->mdbac->connect('BadDriver', self::$TEST_DB);
}

/**
 * Stub for custom database connections
 */
/*
public function testConnectException($driver)
{
	$path = '/mypath/db.conf.xml';
	$driver = 'PDO';
	$database = 'mydb';
	$type = 'PDO';

	$mdbac = new MDBAC($path);
	$dbh = $mdbac->connect($driver, $database);
	$this->assertInstanceOf($type, $dbh);
}
*/

/*=======================================================*/
}
