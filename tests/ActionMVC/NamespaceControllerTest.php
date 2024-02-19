<?php

namespace CustomNamespace;

use PHPUnit\Framework\TestCase;
use Yau\ActionMVC\Controller;

/**
 * Custom controller under namespace
 */
class CustomController extends Controller
{

}

/**
 * Tests for Yau\ActionMVC\Controller
 */
class NamespaceControllerTest extends TestCase
{
/*=======================================================*/

/**
 */
public function testController():void
{
	$controller = new CustomController();
	$controller->setBasePath(__DIR__);

	$view = $controller->get('view');
	unset($view->title);
	$controller->doAction('nospace');
	$this->assertNull($view->title);
	$controller->doAction('namespace');
	$this->assertSame('CustomNamespace', $view->title);
}

/*=======================================================*/
}
