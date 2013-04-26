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
* Class to check that value is valid date time in "YYYY-MM-DD HH:MM:SS" format
*
* @author   John Yau
* @category Yau
* @package  Yau_Validator
*/
class Datetime extends Singleton implements ValidatorInterface
{
/*=======================================================*/

/**
* Regular expression for validating the date time
*
* @var string
*/
const REGEX = '/^(\d{4})\-(0\d|1[0-2])\-([0-2]\d|3[01])\s([01]\d|2[0-3]):([0-5]\d):([0-5]\d)$/';

/**
* Check that a value is a valid date in YYYY-MM-DD HH:MM:SS format
*
* @param  string  $value the value to check
* @return boolean TRUE if check passes, or FALSE if not
* @uses   checkdate()
*/
public function isValid($value)
{
	return (preg_match(self::REGEX, $value, $match)
		&& checkdate($match[2], $match[3], $match[1]));
}

/*=======================================================*/
}
