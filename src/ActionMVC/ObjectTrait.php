<?php declare(strict_types = 1);

namespace Yau\ActionMVC;

/**
 * Trait for ActionMVC objects
 *
 * @author John Yau
 */
trait ObjectTrait
{
/*=======================================================*/

/**
 * Controller
 *
 * @var object
 */
private $controller = null;

/**
 * Return the controller
 *
 * @return object
 */
public function getController()
{
	return $this->controller;
}

/**
 * Set the controller
 *
 * @param object $controller
 */
public function setController($controller): void
{
	$this->controller = $controller;
}

/**
 * Get an object via the controller
 *
 * @param string $type
 * @param string $name
 * @return mixed
 * @uses Controller::get()
 */
public function get($type, $name = 'default')
{
	return (isset($this->controller))
		? $this->controller->get($type, $name)
		: null;
}

/*=======================================================*/
}
