<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Yau\Singleton\Singleton;
use Yau\Singleton\SingletonTrait;

/**
 * Tests for Yau\Singleton\Singleton
 */
class SingletonTest extends TestCase
{
/*=======================================================*/

/**
 */
public function testGetInstance():void
{
	$dt = Singleton::getInstance('DateTime');
	$this->assertInstanceOf('DateTime', $dt);

	$alpha = new class extends Singleton {
	};
	$this->assertInstanceOf(Singleton::class, $alpha);
	$this->assertTrue(method_exists($alpha, 'getInstance'));
	$beta = $alpha::getInstance();
	$this->assertInstanceOf(Singleton::class, $beta);
	$this->assertInstanceOf(get_class($alpha), $beta);
	$gamma = $alpha::getInstance();
	$this->assertSame($beta, $gamma);
}

/**
 */
public function testException():void
{
	$this->expectException(InvalidArgumentException::class);
	Singleton::getInstance('YauSingleton');
}

/**
 */
public function testTrait():void
{
	$alpha = new class {
		use SingletonTrait;
	};
	$this->assertTrue(method_exists($alpha, 'getInstance'));
	$beta = $alpha::getInstance();
	$this->assertInstanceOf(get_class($alpha), $beta);
	$gamma = $beta::getInstance();
	$this->assertSame($beta, $gamma);
}

/*=======================================================*/
}
