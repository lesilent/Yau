<?php declare(strict_types=1);

namespace Yau\ActionMVC;

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
public static function routeProvider(): array
{
	return [
		['/magazine/{volume}/{number}', ['volume'=>'\d+', 'number'=>'\d{1,2}']],
		['/news/{year}/{month}/{day}', ['year'=>'\d{4}', 'month'=>'[1-9]|1[0-2]', 'day'=>'[12]?[1-9]|3[01]']],
		['/news/{news_id}', ['news_id'=>'\w{8}']],
		['/archive/{name}.pdf$', ['name'=>'\w+']],
		['/files/{file}$', ['file'=>'\w[\w\'\s\-\+%]+\w\.\w{3,4}']],
		['/article/{id}', ['id'=>'\w+']],
		['/members', []],
		['/board/files/{role}', ['role'=>'\w+']],
		['/', [], 'index'],
	];
}

/**
 */
public static function setUpBeforeClass(): void
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
public static function pathProvider(): array
{
	return [
		['/magazine/1/2', ['action'=>'magazine', 'volume'=>'1', 'number'=>'2']],
		['/magazine/2/3/', ['action'=>'magazine', 'volume'=>'2', 'number'=>'3']],
		['/magazine/a/', false],
		['/news/2024/1/30', ['action'=>'news', 'year'=>'2024', 'month'=>'1', 'day'=>'30']],
		['/news/2024/2/0', false],
		['/news/2024/3/33', false],
		['/news/abcdefgh', ['action'=>'news', 'news_id'=>'abcdefgh']],
		['/news/abcdefghi', false],
		['/archive/bob.pdf', ['action'=>'archive', 'name'=>'bob']],
		['/archive/dan.pdf?', ['action'=>'archive', 'name'=>'dan']],
		['/archive/joe.pdf?year=2024', ['action'=>'archive', 'name'=>'joe', 'year'=>'2024']],
		['/archive/vic.pdf?action=sub', ['action'=>'archive', 'name'=>'vic']],
		['/files/test.txt', ['action'=>'files', 'file'=>'test.txt']],
		['/files/group - timeline.docx', ['action'=>'files', 'file'=>'group - timeline.docx']],
		['/files/Jan+2020%20log.txt', ['action'=>'files', 'file'=>'Jan+2020 log.txt']],
		['/article/123', ['action'=>'article', 'id'=>'123']],
		['/article/456?ref=parent', ['action'=>'article', 'id'=>'456', 'ref'=>'parent']],
		['/article/789/?ref=child', ['action'=>'article', 'id'=>'789', 'ref'=>'child']],
		['/article/abc/?link[]=2&link[]=4', ['action'=>'article', 'id'=>'abc', 'link'=>['2','4']]],
		['/members', ['action'=>'members']],
		['/members/sdf', ['action'=>'members']],
		['/members+hello', false],
		['/board/files/president', ['action'=>'board_files', 'role'=>'president']],
		['/', ['action'=>'index']],
	];
}

/**
 * @param string $path
 * @param mixed $params
 * @dataProvider pathProvider
 */
public function testMatch($path, $params): void
{
	$router = self::$router;
	$result = $router->match($path);
	$this->assertSame($params, $result);
	if (is_array($params))
	{
		$this->assertIsArray($result);
		$this->assertSame($params, $router->match($path . '#'));
		$this->assertSame($params, $router->match($path . '#hello'));
		if (strpos($path, '?') === false)
		{
			$this->assertSame($params, $router->match($path . '?'));
			$this->assertSame($params + ['hello'=>'world'], $router->match($path . '?hello=world'));
		}
	}
	if (preg_match('/\w/', $path))
	{
		$result = $router->match(preg_replace('/\w+/', '-', $path, 1));
		$this->assertFalse($result);
	}
}

/**
 * @return array
 */
public static function actionProvider(): array
{
	return [
		['magazine', ['volume'=>60, 'number'=>9], '/magazine/60/9'],
		['news', ['year'=>2024,'month'=>3,'day'=>14], '/news/2024/3/14'],
		['news', ['year'=>2024,'month'=>3,'day'=>14, 'hour'=>12], '/news/2024/3/14?hour=12'],
		['news', ['year'=>2024,'month'=>3,'day'=>14, 'hour'=>12, 'minute'=>30], '/news/2024/3/14?hour=12&minute=30'],
		['news', ['news_id'=>'abcdefgh'], '/news/abcdefgh'],
		['members', [], '/members'],
		['members', ['list'=>'settings'], '/members?list=settings'],
		['board_files', ['role'=>'president'], '/board/files/president'],
		['other', [], false],
		['other', ['go'=>'there', 'come'=>'here'], false],
		['index', [], '/'],
	];
}

/**
 * @param string $action
 * @param array $params
 * @param string $path
 * @dataProvider actionProvider
 */
public function testGetPath($action, $params, $path): void
{
	$router = self::$router;
	$this->assertSame($path, $router->getPath($action, $params));
	if (is_string($path) && strcmp(substr($path, -1), '/') != 0)
	{
		$router->useTrailingSlash(true);
		$path = (($pos = strpos($path, '?')) !== false)
			? str_replace('?', '/?', $path)
			: $path . '/';
		$this->assertSame($path, $router->getPath($action, $params));
		$router->useTrailingSlash(false);
	}
}

/**
 * @return array
 */
public static function actionDefaultProvider(): array
{
	return [
		['about', '/about'],
		['blog_list', '/blog/list'],
	];
}

/**
 * @param string $action
 * @param string $path
 * @dataProvider actionDefaultProvider
 */
public function testGetPathDefault($action, $path): void
{
	$router = self::$router;
	$router->useDefaultPaths(true);
	foreach ([false, true] as $slash)
	{
		$router->useTrailingSlash($slash);
		foreach ([[], ['page'=>'2']] as $params)
		{
			$expected = $path . ($slash ? '/' : '') . (empty($params) ? '' : '?' . http_build_query($params));
			$this->assertSame($expected, $router->getPath($action, $params, true));
		}
	}
	$router->useDefaultPaths(false);
	$router->useTrailingSlash(false);
}

/*=======================================================*/
}
