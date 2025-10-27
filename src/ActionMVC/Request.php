<?php declare(strict_types = 1);

namespace Yau\ActionMVC;

use Yau\AccessObject\AccessObject;
use Yau\ActionMVC\ObjectTrait;
use Yau\Singleton\SingletonTrait;

/**
 * Default class used to hold request values that are passed via GET or POST
 *
 * Example
 * <code>
 * use Yau\ActionMVC\Request;
 *
 * $request = Request::getInstance();
 *
 * // The following two are equivalent
 * if (isset($request['fname']))
 * {
 *     echo "fname is passed\n";
 * }
 *
 * if (isset($_GET['fname']) || isset($_POST['fname']))
 * {
 *     echo "fname is passed\n";
 * }
 *
 * // The following two are equivalent
 * $fname = $request['fname'];
 *
 * if (isset($_GET['fname']))
 * {
 *     $fname = trim($_GET['fname']);
 * }
 * elseif (isset($_POST['fname']))
 * {
 *     $fname = trim($_POST['fname']);
 * }
 * else
 * {
 *     $fname = null;
 * }
 * </code>
 *
 * This class should handle arrays that get passed via GET or POST.
 *
 * @author John Yau
 */
class Request extends AccessObject
{
use ObjectTrait, SingletonTrait;
/*=======================================================*/

/**
 * Load request values from environment
 */
public function __construct()
{
	// Use POST and GET if there are any
	if (!empty($_GET) || !empty($_POST))
	{
		// Get POST and GET values
		$values = array_merge($_POST, $_GET);

		// Trim values
		array_walk_recursive($values, fn(&$item) => $item = trim($item));

		// Store values
		$this->assign($values);
	}

	// If command line, then also parse arguments
	if (PHP_SAPI == 'cli')
	{
		$values = $_SERVER['argv'];
		array_shift($values);
		foreach ($values as $arg)
		{
			parse_str($arg, $params);
			if (!empty($params))
			{
				$this->assign($params);
			}
		}
	}

	// Set null to be returned for undefined values
	$this->setUndefinedValue(null);
}

//-------------------------------------

/**
 * Return a variable in super global array
 *
 * @param string $variable the super global variable array name
 * @param string $name     the name of variable in super global array
 * @return mixed
 */
private function getGLOBAL(string $variable, string $name)
{
	return (!empty($this->undefValue) || (isset($GLOBALS[$variable])
		&& is_array($GLOBALS[$variable])
		&& array_key_exists($name, $GLOBALS[$variable])))
		? $GLOBALS[$variable][$name]
		: $this->undefValue;
}

/**
 * Methods for returning variables in super global arrays
 *
 * @param string $name the name of variable in super global array
 * @return mixed
 */
public function getCOOKIE($name) { return $this->getGLOBAL('_COOKIE', $name); }
public function getENV($name) { return $this->getGLOBAL('_ENV', $name); }
public function getFILES($name) { return $this->getGLOBAL('_FILES', $name); }
public function getGET($name) { return $this->getGLOBAL('_GET', $name); }
public function getPOST($name) { return $this->getGLOBAL('_POST', $name); }
public function getREQUEST($name) { return $this->getGLOBAL('_REQUEST', $name); }
public function getSERVER($name) { return $this->getGLOBAL('_SERVER', $name); }
public function getSESSION($name) { return $this->getGLOBAL('_SESSION', $name); }

/**
 * Override method from trait with that of parent
 *
 * @param string $key
 * @return mixed
 */
public function get($key)
{
	return parent::get($key);
}

/*=======================================================*/
}
