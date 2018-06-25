<?php

/**
* Utility Framework
*
* @author   John Yau
* @category Yau
* @package  Yau_ActionMVC
* @version  2007-12-12
*/

namespace Yau\ActionMVC;

use Yau\AccessObject\AccessObject;

/**
* A class used to hold request values that are passed via GET or POST
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
*     $fname = trim(stripslashes($_GET['fname']));
* }
* elseif (isset($_POST['fname']))
* {
*     $fname = trim(stripslashes($_POST['fname']));
* }
* else
* {
*     $fname = NULL;
* }
* </code>
*
* This class should handle arrays that get passed via GET or POST.
*
* @author   John Yau
* @category Yau
* @package  Yau_ActionMVC
*/
class Request extends AccessObject
{
/*=======================================================*/

/**
* Instances
*
* @var array
*/
private static $instances = array();

/**
* Load request values from environment
*/
public function __construct()
{
	// Use POST and GET if there are any
	if (!empty($_GET) || !empty($_POST))
	{
		// GET POST and GET values
		$values = array_merge($_POST, $_GET);

		// Strip slashes if magic quotes are on
		if (get_magic_quotes_gpc())
		{
			$strip_func = function (&$item, $key) {
				$item = stripslashes($item);
			};
			array_walk_recursive($values, $strip_func);
		}

		// Trim values
		$trim_func = function (&$item, $key) {
			$item = trim($item);
		};
		array_walk_recursive($values, $trim_func);

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

	// Set NULL to be returned for undefined values
	$this->setUndefinedValue(NULL);
}

//-------------------------------------

/**
* Return a variable in super global array
*
* @param  string $variable the super global variable array name
* @param  string $name     the name of variable in super global array
* @return mixed
*/
private function getGLOBAL($variable, $name)
{
	if (!empty($this->_undefValue) || (isset($GLOBALS[$variable])
		&& is_array($GLOBALS[$variable])
		&& array_key_exists($name, $GLOBALS[$variable])))
	{
		return $GLOBALS[$variable][$name];
	}
	else
	{
		return $undefValue;
	}
}

/**
* Methods for returning variables in super global arrays
*
* @param  string $name the name of variable in super global array
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
* Return an instance of object
*
* @return object
*/
public static function getInstance()
{
	$class_name = get_called_class();
	if (empty(self::$instances[$class_name]))
	{
		self::$instances[$class_name] = new $class_name();
	}
	return self::$instances[$class_name];
}

/*=======================================================*/
}
