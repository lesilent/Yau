<?php declare(strict_types = 1);

namespace Yau\Validator\Standard;

use Yau\Singleton\Singleton;
use Yau\Validator\ValidatorInterface;

/**
 * Class to check that a value is a valid US social security number (SSN)
 *
 * @author John Yau
 */
class Ssn extends Singleton implements ValidatorInterface
{
/*=======================================================*/

/**
 * Regular expression for validating social security number
 *
 * @var string
 */
const PATTERN = '/^(\d{3})\-?(\d{2})\-?(\d{4})$/';

/**
 * Check that a value is a valid social security number format
 *
 * @param string  $value the value to check
 * @return bool true if check passes, or false if not
 * @link http://en.wikipedia.org/wiki/Social_security_number#Structure
 */
public function isValid($value):bool
{
	return (preg_match(self::PATTERN, $value, $matches)
		&& strcmp($matches[1], '666') != 0	// No Number of the Beast
		&& strcmp($matches[1], '899') <= 0	// Highest area number
		&& strcmp($matches[1], '000') > 0	// No all zeros in digit group
		&& strcmp($matches[2], '00') > 0
		&& strcmp($matches[3], '0000') > 0);
}

/*=======================================================*/
}
