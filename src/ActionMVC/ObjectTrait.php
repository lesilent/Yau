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
 * @var object|null
 */
private $controller = null;

/**
 * Return the controller
 *
 * @return object|null
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
	return (empty($this->controller))
		? null : $this->controller->get($type, $name);
}

/*=======================================================*/
}
