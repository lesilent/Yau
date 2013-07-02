<?php

/**
*
* @category Yau
* @package  Yau_MDBAC
*/

namespace YauTest\MDBAC;

use Yau\MDBAC\Config;

/**
*
* @category Yau
* @package  Yau_MDBAC
*/
class ConfigTest extends \PHPUnit_Framework_TestCase
{
/*=======================================================*/

/**
* Default test database
*
* @var string
*/
private static $TEST_DB = 'projectx';

/**
* Path to the database config file
*
* @var string
*/
protected $filename;

/**
* Random number
*
* @var integer
*/
private $randnum;

/**
*
* @var object
*/
protected $config;

/**
*
*/
public function setUp()
{
	$this->filename = __DIR__ . DIRECTORY_SEPARATOR . 'db.conf.xml';
	$this->randnum = mt_rand(0, mt_getrandmax());
	$this->config = new Config($this->filename);
}

/**
* @expectedException Yau\MDBAC\Exception\InvalidArgumentException
* @dataProvider      providerConstructorException
*/
public function testConstructorException($xml, array $options = array())
{
	$config = @new Config($xml, $options);
}

/**
* @return array
*/
public function providerConstructorException()
{
	return array(
		array(NULL),
		array('badfile' . $this->randnum),
		array('<?xml version="'),
	);
}

/**
* @see Yau\MDBAC\Config::isValidFile()
*/
public function testIsValidFile()
{
	$this->assertTrue(@Config::isValidFile($this->filename));
	$this->assertFalse(@Config::isValidFile($this->filename . $this->randnum));
}

/**
* @see Yau\MDBAC\Config::isValidXml()
*/
public function testIsValidXml()
{
	$xml = file_get_contents($this->filename);
	$this->assertTrue(@Config::isValidXml($xml));
	$this->assertFalse(@Config::isValidXml($xml . $this->randnum));
	$this->assertFalse(@Config::isValidXml($this->randnum . $xml));
	$this->assertFalse(@Config::isValidXml(strrev($xml)));
}

/**
* @see Yau\MDBAC\Config::fetchOne()
*/
public function testFetchOne()
{
	$result = $this->config->fetchOne(self::$TEST_DB);
	$this->assertTrue(is_array($result));
	foreach (array('system', 'host', 'name', 'driver', 'dbname', 'username', 'password') as $field)
	{
		$this->assertArrayHasKey($field, $result);
	}
}

/**
* @see Yau\MDBAC\Config::fetchAll()
*/
public function testFetchAll()
{
	$result = $this->config->fetchAll(self::$TEST_DB);
	$this->assertInternalType('array', $result);
}

/**
* @see Yau\MDBAC\Config::query()
*/
public function testQuery()
{
	$result = $this->config->query(self::$TEST_DB);
	$this->assertInstanceOf('Yau\MDBAC\Result', $result);
}

/*=======================================================*/
}
