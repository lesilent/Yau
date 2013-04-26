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
* Class to check that a value is a valid US zip code format
*
* @author   John Yau
* @category Yau
* @package  Yau_Validator
*/
class Zip extends Singleton implements ValidatorInterface
{
/*=======================================================*/

/**
* Regular expression for validating US zip code
*
* @var string
*/
const REGEX = '/^(\d{5})(?:\-(\d{4}))?$/';

/**
* Check that a value is a valid US zip code
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
