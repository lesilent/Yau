<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Yau\Db\Connect\Connect;
use Yau\Db\Adapter\Adapter;
use Yau\Db\Adapter\Driver\AbstractDriver as AdapterDriver;
use Yau\Db\Statement\Driver\AbstractDriver as StatementDriver;

/**
 * Tests for Yau\Db\Connect\Connect
 */
class ConnectTest extends TestCase
{
/*=======================================================*/

/**
 * Known drivers
 *
 * @var array
 */
private static $KNOWN_DRIVERS = ['cli', 'pdo_mysql'];

/**
 */
public function testAvailableDrivers():void
{
	$drivers = Connect::getAvailableDrivers();
	$this->assertIsArray($drivers);
	foreach (self::$KNOWN_DRIVERS as $driver)
	{
		$this->assertContains($driver, $drivers);
	}
}

/**
 */
public function testFactory():void
{
	foreach (Connect::getAvailableDrivers() as $driver)
	{
		if (preg_match('/^cli(?:_(\w+))?$/', $driver, $matches))
		{
			$params = empty($matches[1]) ? ['driver'=>'mysql'] : [];
			$cmd = Connect::factory($driver, $params);
			$this->assertIsString($cmd);
			$cmd2 = Connect::factory(strtoupper($driver), $params);
			$this->assertSame($cmd, $cmd2);

			$sub_driver = empty($matches[1]) ? $params['driver'] : $matches[1];
			if (in_array($sub_driver, ['db2', 'mysql']))
			{
				$this->assertSame(1, preg_match('/^' . $sub_driver . '\b/', $cmd));
			}
		}
	}
}

/**
 * @return array
 */
public function badConnectProvider():array
{
	$arguments = [];
	foreach (Connect::getAvailableDrivers() as $driver)
	{
		if (preg_match('/^pdo(?:_(\w+))?$/', $driver, $matches))
		{
			$params = [
				'host'     => 'localhost',
				'dbname'   => 'db_' . mt_rand(0, getrandmax()),
				'username' => uniqid('un_'),
				'password' => uniqid('pw_'),
			];
			if (empty($matches[1]))
			{
				$params['driver'] = 'mysql';
			}
			$arguments[] = [$driver, $params];
		}
	}
	return $arguments;
}

/**
 * @param string $driver
 * @param array  $params
 * @dataProvider badConnectProvider()
 */
public function testBadConnect($driver, $params):void
{
	$this->expectException(RuntimeException::class);
	Connect::factory($driver, $params);
}

/**
 * @return iterable
 */
public function connectProvider():iterable
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
		yield [$params];
		$i++;
	}
	while (true);
}

/**
 * @param array $params
 * @throws Exception if invalid connect configuration
 * @dataProvider connectProvider()
 */
public function testConnect($params):void
{
	$dbh = Connect::factory('PDO', $params);
	$this->assertNotFalse($dbh);
	if (is_object($dbh) || is_resource($dbh))
	{
		$dbh = Adapter::factory($dbh);
		$this->assertInstanceOf(AdapterDriver::class, $dbh);

		$sth = $dbh->prepare('SELECT ? AS num');
		$this->assertInstanceOf(StatementDriver::class, $sth);
		$id = uniqid();

		// Test fetchOne
		$sth->execute([$id]);
		$this->assertSame($id, $sth->fetchOne());

		// Test fetchRow
		$sth->execute([$id]);
		$row = $sth->fetchRow();
		$this->assertIsArray($row);
		$this->assertCount(1, $row);
		$this->assertArrayHasKey('num', $row);
		$this->assertSame($id, reset($row));

		// Test fetchAll
		$sth->execute([$id]);
		$result = $sth->fetchAll();
		$this->assertIsArray($row);
		$this->assertCount(1, $row);
		$row = reset($result);
		$this->assertIsArray($row);
		$this->assertCount(1, $row);
		$this->assertArrayHasKey('num', $row);
		$this->assertSame($id, reset($row));
	}
}

/*=======================================================*/
}
