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
* Class to check that a value is a valid time in "HH:MM:SS" format
*
* @author   John Yau
* @category Yau
* @package  Yau_Validator
*/
class Time extends Singleton implements ValidatorInterface
{
/*=======================================================*/

/**
* Regular expression for validating the time
*
* @var string
*/
const REGEX = '/^([01]\d|2[0-3]):([0-5]\d):([0-5]\d)$/';

/**
* Check that a value is a valid time in HH:MM:SS format
*
* @param  string  $value the value to check
* @return boolean TRUE if check passes, or FALSE if not
*/
public function isValid($value)
{
	return (bool) preg_match(self::REGEX, $value, $match);
}

/*=======================================================*/
}
