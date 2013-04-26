<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_Validator
*/

namespace Yau\Validator;

use Yau\Singleton\Singleton;
use Yau\Validator\Exception\InvalidArgumentException;

/**
* Central class for handling standard validator objects
*
* Example
* <code>
* use Yau\Validator\StandardValidator;
*
* $sv = new StandardValidator();
* echo $sv->isValidSsn('123123123') ? 'YES' : 'NO';
* </code>
*
* @author   John Yau
* @category Yau
* @package  Yau_Validator
*/
class StandardValidator extends Singleton
{
/*=======================================================*/

/**
* Array of validators
*
* @var array
*/
private $validators = array();

/**
* Array of constructor options
*
* @var array
*/
private $options = array();

/**
* Constructor
*/
public function __construct($options = array())
{
	$this->options = array();
}

/**
* Magic method for routing validator calls to respective classes
*
* @param  string $func
* @param  array  $args
* @throws Exception if method is invalid
*/
public function __call($func, $args)
{

	if (!preg_match('/^isValid([A-Z][a-z]+)$/', $func, $match))
	{
		throw new InvalidArgumentException('Invalid method ' . $func);
	}

	// Instantiate validator
	$vname = $match[1];
	$vkey  = strtolower($match[1]);
	if (empty($this->validators[$vname]))
	{
		// Return FALSE if argument isn't scalar
		if (empty($args) || !is_scalar($args[0]))
		{
			return FALSE;
		}

		// Load class
		$class_name = 'Yau\\Validator\\Standard\\' . $vname;
		if (!class_exists($class_name, FALSE))
		{
			$filename = __DIR__ . DIRECTORY_SEPARATOR
				. 'Standard' . DIRECTORY_SEPARATOR
				. $vname . '.php';
			require $filename;
		}

		// Instantiate object
		if (empty($this->options[$vkey]))
		{
			$this->validators[$vkey] = $class_name::getInstance();
		}
		else
		{
			$reflect = new ReflectionClass($class_name);
			$this->validators[$vkey] = $reflect->newInstanceArgs($this->options[$vkey]);
			unset($this->options[$vkey]);
		}
	}

	// Call validator
	$validator = $this->validators[$vkey];
	return call_user_func_array(array($validator, 'isValid'), $args);
}

/**
* Return an array of available validators
*
* @return array
*/
public static function getAvailableValidators()
{
	// Iteratate over directory to find files
	$validators = array();
	$iterator = new \FilesystemIterator(__DIR__ . DIRECTORY_SEPARATOR . 'Standard', \FilesystemIterator::KEY_AS_FILENAME);
	foreach ($iterator as $filename => $finfo)
	{
		if (preg_match('/^\w+\.php$/', $filename)
			&& $finfo->isFile())
		{
			$validators[] = strtolower($finfo->getBasename('.php'));
		}
	}

	// Return validators
	return $validators;
}

/*=======================================================*/
// End Util_Validate_Check_Time
}
