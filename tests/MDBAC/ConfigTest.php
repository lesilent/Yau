<?php declare(strict_types=1);

namespace Yau\MDBAC;

use PHPUnit\Framework\TestCase;
use Yau\MDBAC\Config;
use Yau\MDBAC\Result;
use InvalidArgumentException;

/**
 * Tests for Yau\MDBAC\Config
 */
class ConfigTest extends TestCase
{
/*=======================================================*/

/**
 * Default test database
 *
 * @var string
 */
private static $TEST_DB = 'projectx';

/**
 * Name of xml file
 *
 * @var string
 */
private static $XML_FILE = 'db.conf.xml';

/**
 * Name of json file
 *
 * @var string
 */
private static $JSON_FILE = 'db.conf.json';

/**
 * Path to the database config file
 *
 * @var string
 */
private static $filename;

/**
 * @var object
 */
private static $config;

/**
 * Fields in database info array
 *
 * @var array
 */
private static $DB_FIELDS = ['system', 'host', 'name', 'driver', 'dbname', 'username', 'password'];

/**
 */
public static function setUpBeforeClass(): void
{
	self::$filename = __DIR__ . DIRECTORY_SEPARATOR . self::$XML_FILE;
	self::$config = new Config(self::$filename);
}

/**
 * @return array
 */
public function providerConstructorException(): array
{
	return [
		[null],
		['badfile' . mt_rand(0, getrandmax())],
		['{}'],
		['<?xml version="'],
	];
}

/**
 * @dataProvider providerConstructorException
 */
public function testConstructorException($cfg, array $options = []): void
{
	$this->expectException(InvalidArgumentException::class);
	$config = new Config($cfg, $options);
}

/**
 * @return array
 */
public function providerConfigFile(): array
{
	return [
		[__DIR__ . DIRECTORY_SEPARATOR . self::$XML_FILE],
		[__DIR__ . DIRECTORY_SEPARATOR . self::$JSON_FILE],
	];
}

/**
 * Return random bytes
 *
 * @return string
 */
private function getRandomString(): string
{
	return str_replace("\0", '', random_bytes(8));
}

/**
 * @param string $filename
 * @dataProvider providerConfigFile
 */
public function testIsValidFile($filename): void
{
	$error = null;
	$this->assertTrue(Config::isValidFile($filename, $error), $error ?? 'Invalid file ' . $filename);
	$this->assertNull($error);
	$error = null;
	$this->assertFalse(Config::isValidFile($filename . $this->getRandomString(), $error));
	$this->assertIsString($error);
	$error = null;
	$this->assertFalse(Config::isValidFile($this->getRandomString() . $filename, $error));
	$this->assertIsString($error);
}

/**
 */
public function testIsValidJson(): void
{
	$json = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . self::$JSON_FILE);
	$error = null;
	$this->assertTrue(Config::isValidJson($json, $error), $error ?? 'Invalid json file');
	$this->assertNull($error);
	$error = null;
	$this->assertFalse(Config::isValidJson($json . $this->getRandomString(), $error));
	$this->assertIsString($error);
	$error = null;
	$this->assertFalse(Config::isValidJson($this->getRandomString() . $json, $error));
	$this->assertIsString($error);
	$error = null;
	$this->assertFalse(Config::isValidJson(strrev($json), $error));
	$this->assertIsString($error);
}

/**
 */
public function testIsValidXml(): void
{
	$xml = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . self::$XML_FILE);
	$error = null;
	$this->assertTrue(Config::isValidXml($xml, $error), $error ?? 'Invalid xml file');
	$this->assertNull($error);
	$error = null;
	$this->assertFalse(Config::isValidXml($xml . random_bytes(8), $error));
	$this->assertIsString($error);
	$error = null;
	$this->assertFalse(Config::isValidXml(random_bytes(8) . $xml, $error));
	$this->assertIsString($error);
	$error = null;
	$this->assertFalse(Config::isValidXml(strrev($xml), $error));
	$this->assertIsString($error);
}

/**
 * @return iterable
 */
public function providerConfig(): iterable
{
	foreach ($this->providerConfigFile() as $row)
	{
		yield [new Config($row[0])];
	}
}

/**
 * @param \Yau\MDBAC\Config $config
 * @dataProvider providerConfig
 */
public function testFetchAll($config): void
{
	$result = $config->fetchAll(self::$TEST_DB);
	$this->assertIsArray($result);
}

/**
 * @return iterable
 */
public function providerFetchAllWithOptions(): iterable
{
	foreach ($this->providerConfigFile() as $row)
	{
		$config = new Config($row[0]);
		yield [$config, 'projectx', [], ['primary']];
		yield [$config, 'projectx', ['access'=>'read'], ['primary', 'replica2']];
		yield [$config, 'projectx', ['system'=>'replica2'], []];
		yield [$config, 'projectx', ['access'=>'read', 'system'=>'replica2'], ['replica2']];
		yield [$config, 'projectx', ['system'=>'invalid'], []];
		yield [$config, 'warehouse', ['access'=>'read'], ['replica2', 'replica3', 'replica4']];
	}
}

/**
 * @param \Yau\MDBAC\Config $config
 * @param string $database
 * @param array  $options
 * @param array  $expected
 * @dataProvider providerFetchAllWithOptions
 */
public function testFetchAllWithOptions($config, $database, $options, $expected): void
{
	$result = $config->fetchAll($database, $options);
	$this->assertIsArray($result);
	$systems = [];
	foreach ($result as $row)
	{
		$systems[] = $row['system'];
	}
	$this->assertSame(count($expected), count($systems));
	$this->assertEmpty(array_diff($expected, $systems));
}

/**
 * @param \Yau\MDBAC\Config $config
 * @dataProvider providerConfig
 */
public function testFetchOne($config): void
{
	$result = $config->fetchOne(self::$TEST_DB);
	$this->assertIsArray($result);
	foreach (self::$DB_FIELDS as $field)
	{
		$this->assertArrayHasKey($field, $result);
	}
}

/**
 * @return iterable
 */
public function providerFetchOneWithOptions(): iterable
{
	foreach ($this->providerConfigFile() as $row)
	{
		$config = new Config($row[0]);
		yield [$config, 'projectx', [], ['username'=>'webuser']];
		yield [$config, 'projectx', ['username'=>'webuser'], ['username'=>'webuser']];
		yield [$config, 'projectx', ['username'=>'admin'], ['username'=>'admin']];
		yield [$config, 'projectx', ['username'=>'admin', 'system'=>'primary'], ['username'=>'admin', 'system'=>'primary']];
		yield [$config, 'projectx', ['username'=>'admin', 'system'=>'replica1'], null];
		yield [$config, 'projectx', ['username'=>'admin', 'system'=>'replica2'], null];
		yield [$config, 'projectx', ['username'=>'admin', 'system'=>'replica2', 'access'=>'read'],['username'=>'admin', 'system'=>'replica2']];
		yield [$config, 'projectx', ['username'=>'admin', 'system'=>'invalid'], null];
		yield [$config, 'projectx', ['username'=>'invalid'], null];
		yield [$config, 'projectx', ['access'=>'read'], ['username'=>'report', 'system'=>'replica2']];
	}
}

/**
 * @param \Yau\MDBAC\Config $config
 * @param string $database
 * @param array  $options
 * @param mixed  $expected
 * @dataProvider providerFetchOneWithOptions
 */
public function testFetchOneWithOptions($config, $database, $options, $expected): void
{
	$result = $config->fetchOne($database, $options);
	if (empty($expected))
	{
		$this->assertSame($expected, $result);
	}
	else
	{
		$this->assertIsArray($result);
		foreach ($expected as $attrib => $value)
		{
			$this->assertArrayHasKey($attrib, $result);
			$this->assertSame($value, $result[$attrib]);
		}
	}
}

/**
 * Due to weights some systems should appear more frequently than others
 *
 * @param \Yau\MDBAC\Config $config
 * @dataProvider providerConfig
 */
public function testFetchOneWithWeights($config): void
{
	$counts = array_fill_keys(['replica2', 'replica3', 'replica4'], 0);
	for ($i = 0; $i < 1000; $i++)
	{
		$result = $config->fetchOne('warehouse', ['access'=>'read']);
		$counts[$result['system']]++;
	}
	$this->assertLessThan($counts['replica2'], $counts['replica3']);
	$this->assertLessThan($counts['replica2'], $counts['replica4']);
}

/**
 * @param \Yau\MDBAC\Config $config
 * @dataProvider providerConfig
 */
public function testQuery($config): void
{
	$result = $config->query(self::$TEST_DB);
	$this->assertInstanceOf(Result::class, $result);
	while ($db = $result->fetch())
	{
		$this->assertIsArray($db);
		foreach (self::$DB_FIELDS as $field)
		{
			$this->assertArrayHasKey($field, $db);
		}
	}
}

/*=======================================================*/
}
