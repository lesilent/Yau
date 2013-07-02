<?php

/**
* Yau Tools Tests
*
* @author John Yau
*/
namespace YauTest\MDBAC;

use Yau\MDBAC\MDBAC;
use Yau\MDBAC\Exception\ConnectException;

/**
*
* @author John Yau
* @todo   add tests using sqlite
*/
class MDBACTest extends \PHPUnit_Framework_TestCase
{
/*=======================================================*/

/**
* The database used for testing
*
* @var string
*/
private static $TEST_DB = 'projectx';

/**
* Database string for a working database
*
* @var string
*/
private static $WORK_DB = 'yautest';

/**
* The MDBAC object
*
* @var object
*/
protected $mdbac;

/**
*
*/
public function setUp()
{
	$this->mdbac = new MDBAC(__DIR__ . DIRECTORY_SEPARATOR . 'db.conf.xml');
}

/**
*
* @dataProvider providerDriverConstants
*/
public function testDriverConstants($constname)
{
	$this->assertTrue(defined('Yau\MDBAC\MDBAC::' . $constname));
}

/**
*
*/
public function testGetConfig()
{
	$this->assertInstanceOf('Yau\MDBAC\Config', $this->mdbac->getConfig());
}

/**
*
*/
public function testConnect()
{
	$this->assertTrue(is_string($this->mdbac->connect('CLI', self::$TEST_DB)));
	$this->assertTrue(is_string($this->mdbac->connect('CLI', self::$WORK_DB)));
	$this->assertInstanceOf('PDO', $this->mdbac->connect('PDO', self::$WORK_DB));
}

/**
* @expectedException Yau\MDBAC\Exception\InvalidArgumentException
*/
public function testInvalidDriverException()
{
	$dbh = $this->mdbac->connect('BadDriver', self::$TEST_DB);
}

/**
* @dataProvider providerDriverConstants
* @expectedException Yau\MDBAC\Exception\ConnectException
*/
public function testConnectException($driver)
{
	if (in_array($driver, array('DBX', 'MSSQL')) && !function_exists(strtolower($driver) . '_connect')) throw new ConnectException('No connect function exists for ' . $driver);
	if (!class_exists('PEAR', FALSE) and preg_match('/^PEAR_/', $driver)) throw new ConnectException('No PEAR class defined for ' . $driver);
	if (preg_match('/^(CLI|PERL)_?/', $driver)) throw new ConnectException($driver . ' cannot be connected directly');
	$this->mdbac->connect($driver, self::$TEST_DB);
}

/**
* @return array
*/
public function providerDriverConstants()
{
	// Get path to the MDBAC Connect classes
	$reflect = new \ReflectionClass('Yau\Db\Connect\Connect');
	$path = dirname($reflect->getFileName()) . DIRECTORY_SEPARATOR . 'Driver';
	$pathlen = strlen($path);

	// Iterate over the files to get drivers
	$drivers = array();
	$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
	foreach ($iterator as $filename)
	{
		if (is_file($filename)
			&& ($dirpos = strpos($filename, DIRECTORY_SEPARATOR, $pathlen + 1)) !== FALSE
			&& ($constname = basename(str_replace(DIRECTORY_SEPARATOR, '_', substr($filename, $dirpos + 1)), '.php'))
			&& !preg_match('/Interface$/i', $constname))
		{
			$drivers[] = array(strtoupper($constname));
		}
	}

	// Return drivers
	return $drivers;
}

/*=======================================================*/
}
