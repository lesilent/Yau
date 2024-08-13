<?php declare(strict_types=1);

namespace Yau\ActionMVC;

use PHPUnit\Framework\TestCase;
use Yau\ActionMVC\Request;

/**
 * Tests for Yau\ActionMVC\Request
 *
 * @backupGlobals enabled
 */
class RequestTest extends TestCase
{
/*=======================================================*/

/**
 * @var array
 */
private static $variables;

/**
 * @var object
 */
private static $request;

/**
 * @return array
 */
public static function superProvider():array
{
	return [
		['COOKIE'],
		['ENV'],
		['FILES'],
		['GET'],
		['POST'],
		['REQUEST'],
		['SERVER'],
		['SESSION'],
	];
}

/**
 */
public static function setUpBeforeClass():void
{
	self::$variables = [];
	foreach (self::superProvider() as $data)
	{
		$super = reset($data);
		$value = '  ' . uniqid() . '  ';
		$name = uniqid($super);
		$GLOBALS['_' . $super][$name] = $value;
		self::$variables[$super] = [$name, $value];
	}
	self::$request = new Request();
}

/**
 */
public static function tearDownAfterClass():void
{
	foreach (self::$variables as $super => $data)
	{
		$name = reset($data);
		unset($GLOBALS['_' . $super][$name]);
	}
	self::$variables = [];
	self::$request = null;
}

/**
 * @param string $super
 * @dataProvider superProvider
 */
public function testGetter($super):void
{
	$request = self::$request;
	$method = 'get' . $super;
	list($name, $value) = self::$variables[$super];
	$this->assertSame($value, $request->$method($name));
	if (in_array($super, ['GET', 'POST']))
	{
		$this->assertSame(trim($value), $request->$name);
		$this->assertSame(trim($value), $request[$name]);
	}
	else
	{
		$this->assertNull($request->$name);
		$this->assertNull($request[$name]);
	}
}

/*=======================================================*/
}
