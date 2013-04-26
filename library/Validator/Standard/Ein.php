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
* Class to check that a value is a valid US employer identification number (EIN)
*
* @author   John Yau
* @category Yau
* @package  Yau_Validator
*/
class Ein extends Singleton implements ValidatorInterface
{
/*=======================================================*/

/**
* Regular expression for validating employer identification number
*
* @var string
*/
const REGEX = '/^(\d{2})\-?(\d{7})$/';

/**
* Check that a value is a valid employer identification number format
*
* @param  string  $value the value to check
* @return boolean TRUE if check passes, or FALSE if not
* @link   http://en.wikipedia.org/wiki/Employer_Identification_Number#EIN_format
*/
public function isValid($value)
{
	return (preg_match(self::REGEX, $value, $match) && (
		($match[1] >= 1 && $match[1] <= 6)
			|| ($match[1] >= 10 && $match[1] <= 16)
			|| ($match[1] >= 20 && $match[1] <= 27)
			|| ($match[1] >= 30 && $match[1] <= 48)
			|| ($match[1] >= 50 && $match[1] <= 68)
			|| ($match[1] >= 71 && $match[1] <= 77)
			|| ($match[1] >= 80 && $match[1] <= 88)
			|| ($match[1] >= 90 && $match[1] <= 95)
			|| ($match[1] >= 98 && $match[1] <= 99)
		));
}

/*=======================================================*/
}
