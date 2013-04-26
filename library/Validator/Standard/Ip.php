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
* Class to check that a value is a valid ip address
*
* @author   John Yau
* @category Yau
* @package  Yau_Validator
*/
class Ip extends Singleton implements ValidatorInterface
{
/*=======================================================*/

/**
* Regular expression for validating a ipv4 address
*
* @var string
*/
const REGEX = '/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/';

/**
* Check that a value is a valid ip address
*
* @param  mixed   $value the value to check
* @return boolean TRUE if check passes, or FALSE if not
*/
public function isValid($value)
{
	return (preg_match(self::REGEX, $value, $match)
		&& $match[1] <= 255
		&& $match[2] <= 255
		&& $match[3] <= 255
		&& $match[4] <= 255);
}

/*=======================================================*/
}
