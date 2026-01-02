<?php declare(strict_types=1);

namespace Yau\ActionMVC;

use PHPUnit\Framework\TestCase;
use Yau\ActionMVC\Controller;
use Yau\ActionMVC\View;

/**
 * Tests for Yau\ActionMVC\View
 */
class ViewTest extends TestCase
{
/*=======================================================*/

/**
 * @var object|null
 */
private static $view;

/**
 * Te default content
 *
 * @var string
 */
private static $CONTENT = 'Hello World';

/**
 */
public static function setUpBeforeClass(): void
{
	self::$view = View::getInstance();
	self::$view->setBasePath(__DIR__ . DIRECTORY_SEPARATOR . 'templates');
	self::$view->content = self::$CONTENT;
}

/**
 */
public static function tearDownAfterClass(): void
{
	self::$view = null;
}

/**
 * @return array
 */
public static function templateProvider(): array
{
	return [
		['header', '<html><body>'],
		['body', '<div>' . self::$CONTENT . '</div>'],
		['footer', '</body></html>'],
	];
}

/**
 * @param string $template
 * @param string $html
 * @dataProvider templateProvider
 */
public function testView(string $template, string $html): void
{
	$view = self::$view;

	$str = $view->render($template);
	$this->assertIsString($str);
	$this->assertEquals($html, trim($str));

	$controller = new Controller();
	$view->setController($controller);
	$this->assertSame($controller, $view->getController());
}

/*=======================================================*/
}
