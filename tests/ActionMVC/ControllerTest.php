<?php declare(strict_types=1);

namespace Yau\ActionMVC;

use PHPUnit\Framework\TestCase;
use Yau\ActionMVC\Controller;
use Yau\ActionMVC\Request;
use Yau\ActionMVC\View;

/**
 * Tests for Yau\ActionMVC\Controller
 *
 * @backupGlobals enabled
 */
class ControllerTest extends TestCase
{
/*=======================================================*/

/**
 */
public function testController(): void
{
	$id = uniqid();
	$controller = new class extends Controller {
		private $id;
		public function getId()
		{
			return $this->id;
		}
		public function setId($id)
		{
			$this->id = $id;
		}
	};

	$request = $controller->get('request');
	$this->assertInstanceOf(Request::class, $request);
	$view = $controller->get('view');
	$this->assertInstanceOf(View::class, $view);

	$helper = new class {};
	$controller->set('globalhelp', $helper);
	$controller->set('helper', 'special', $helper);
	$this->assertSame($helper, $controller->get('globalhelp'));
	$this->assertSame($helper, $controller->get('helper', 'special'));

	$controller->setBasePath(__DIR__);
	$controller->doAction('default');
	$this->assertSame('default', $view->title);  // @phpstan-ignore property.notFound
	$controller->doAction('login');
	$this->assertSame('login', $view->title);

	$controller->setId($id);
	$controller->doAction('show');
	$this->assertSame($id, $view->title);
}

/*=======================================================*/
}
