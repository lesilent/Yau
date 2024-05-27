<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Yau\ActionMVC\Router;

/**
 * Tests for Yau\ActionMVC\Router
 */
class RouterTest extends TestCase
{
/*=======================================================*/

/**
 * @var object
 */
private static $router;

/**
 * @return array
 */
public static function routeProvider():array
{
	return [
		['/magazine/{volume}/{number}', ['volume'=>'\d+', 'number'=>'\d{1,2}']],
		['/news/{year}/{month}/{day}', ['year'=>'\d{4}', 'month'=>'[1-9]|1[0-2]', 'day'=>'[12]?[1-9]|3[01]']],
		['/archive/{name}.pdf', ['name'=>'\w+']],
		['/members', []],
	];
}

/**
 */
public static function setUpBeforeClass():void
{
	self::$router = new Router();
	foreach (self::routeProvider() as $args)
	{
		call_user_func_array([self::$router, 'addRoute'], $args);
	}
}

/**
 * @return array
 */
public static function pathProvider():array
{
	return [
		['/magazine/1/2', ['action'=>'magazine', 'volume'=>'1', 'number'=>'2']],
		['/magazine/2/3/', ['action'=>'magazine', 'volume'=>'2', 'number'=>'3']],
		['/magazine/a/', false],
		['/news/2024/1/30', ['action'=>'news', 'year'=>'2024', 'month'=>'1', 'day'=>'30']],
		['/news/2024/2/0', false],
		['/news/2024/3/33', false],
		['/archive/bob.pdf', ['action'=>'archive', 'name'=>'bob']],
		['/archive/joe.pdf?', ['action'=>'archive', 'name'=>'joe']],
		['/members', ['action'=>'members']],
		['/members/sdf', ['action'=>'members']],
		['/members+hello', false],
	];
}

/**
 * @param string $path
 * @dataProvider pathProvider
 */
public function testMatch($path, $params):void
{
	$router = self::$router;
	$result = $router->match($path);
	$this->assertSame($params, $result);
	if (is_array($params))
	{
		$this->assertIsArray($result);
		$this->assertSame($params, $router->match($path . '?'));
		$this->assertSame($params, $router->match($path . '?hello=world'));
	}
	$result = $router->match(preg_replace('/\w+/', '-', $path, 1));
	$this->assertFalse($result);
}

/*=======================================================*/
}
