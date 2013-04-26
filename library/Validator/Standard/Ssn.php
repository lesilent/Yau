<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_Validator
*/

namespace Yau\Validator\Standard;

use Yau\Singleton\Singleton;
use Yau\Validator\ValidatorInterface;

/**
* Class to check that a value is a valid US social security number (SSN)
*
* @author   John Yau
* @category Yau
* @package  Yau_Validator
*/
class Ssn extends Singleton implements ValidatorInterface
{
/*=======================================================*/

/**
* Regular expression for validating social security number
*
* @var string
*/
const REGEX = '/^(\d{3})\-?(\d{2})\-?(\d{4})$/';

/**
* Check that a value is a valid social security number format
*
* @param  string  $value the value to check
* @return boolean TRUE if check passes, or FALSE if not
* @link   http://en.wikipedia.org/wiki/Social_security_number#Structure
*/
public function isValid($value)
{
	return (preg_match(self::REGEX, $value, $match)
		&& $match[1] > 0 && $match[2] > 0 && $match[3] > 0   // No all zeros in digit group
		&& $match[1] != 666                                  // No Number of the Beast
		&& $match[1] <= 772                                  // Highest area number
		&& !($match[1] >= 734 && $match[1] <= 749)
		);
}

/*=======================================================*/
}
