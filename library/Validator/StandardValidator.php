<?php declare(strict_types = 1);

namespace Yau\Validator;

use Yau\Singleton\Singleton;
use InvalidArgumentException;
use FilesystemIterator;

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
 * @author John Yau
 */
class StandardValidator extends Singleton
{
/*=======================================================*/

/**
 * Array of validators
 *
 * @var array
 */
private $validators = [];

/**
 * Magic method for routing validator calls to respective classes
 *
 * @param string $func
 * @param array  $args
 * @return bool
 * @throws InvalidArgumentException if method is invalid
 */
public function __call($func, $args)
{
	if (!preg_match('/^isValid([A-Z][a-z]+)$/', $func, $matches))
	{
		throw new InvalidArgumentException('Invalid method ' . $func);
	}

	// Instantiate validator
	$vname = $matches[1];
	if (empty($this->validators[$vname]))
	{
		// Return false if argument isn't scalar
		if (empty($args) || !is_scalar($args[0]))
		{
			return false;
		}

		// Load class
		$class_name = 'Yau\\Validator\\Standard\\' . $vname;
		if (!class_exists($class_name, false))
		{
			$filename = __DIR__ . DIRECTORY_SEPARATOR
				. 'Standard' . DIRECTORY_SEPARATOR
				. $vname . '.php';
			require $filename;
		}

		// Instantiate object
		$this->validators[$vname] = $class_name::getInstance();
	}

	// Call validator
	return call_user_func_array([$this->validators[$vname], 'isValid'], $args);
}

/**
 * Return an array of available validators
 *
 * @return array
 */
public static function getAvailableValidators():array
{
	// Iteratate over directory to find files
	$validators = [];
	$iterator = new FilesystemIterator(__DIR__ . DIRECTORY_SEPARATOR . 'Standard', FilesystemIterator::KEY_AS_FILENAME);
	foreach ($iterator as $filename => $finfo)
	{
		if (preg_match('/^\w+\.php$/', $filename) && $finfo->isFile())
		{
			$validators[] = strtolower($finfo->getBasename('.php'));
		}
	}
	sort($validators);

	// Return validators
	return $validators;
}

/*=======================================================*/
}
