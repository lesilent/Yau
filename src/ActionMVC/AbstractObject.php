<?php declare(strict_types = 1);

namespace Yau\ActionMVC;

/**
 * Abstract parent MVC object
 *
 * @author John Yau
 */
abstract class AbstractObject
{
/*=======================================================*/

/**
 * Controller
 *
 * @var object|null
 */
private $controller = null;

/**
 * Action initialization steps can be placed in this function
 *
 * Note: This method is called after object has been instantiated
 * @deprecated
 */
public function init(): void
{
	// Additional logic goes here
}

/**
 * Clean up action for destruction
 *
 * Note: This method is called by the destructor
 *
 * @deprecated
 */
public function cleanup(): void
{
	// Additional logic goes here
}

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
public function setController($controller)
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

/**
 * Destructor
 */
public function __destruct()
{
	$this->cleanup();
	unset($this->controller);
}

/*=======================================================*/
}
