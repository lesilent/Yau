<?php declare(strict_types=1);

namespace Yau\Db;

use PHPUnit\Framework\TestCase;
use Yau\Db\Adapter\Adapter;

/**
 * Tests for Yau\Db\Adapter\Adapter
 */
class AdapterTest extends TestCase
{
/*=======================================================*/

/**
 * Known drivers
 *
 * @var array
 */
private static $KNOWN_DRIVERS = ['mysqli', 'pdo_mysql'];

/**
 */
public function testGetAvailableDrivers():void
{
	$drivers = Adapter::getAvailableDrivers();
	$this->assertIsArray($drivers);
	foreach (self::$KNOWN_DRIVERS as $driver)
	{
		$this->assertContains($driver, $drivers);
	}
}

/*=======================================================*/
}
