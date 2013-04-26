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
* Class to check that a value is a valid url
*
* Note: this currently only checks HTTP urls
*
* @author   John Yau
* @category Yau
* @package  Yau_Validator
*/
class Url extends Singleton implements ValidatorInterface
{
/*=======================================================*/

/**
* Regular expression for validating a url format
*
* @var string
*/
const REGEX = '/^https?:\/\/[\w\-]+\.\w+/i';

/**
* Check that a value is a valid url
*
* @param  mixed   $value the value to check
* @return boolean TRUE if check passes, or FALSE if not
*/
public function isValid($value)
{
	return (filter_var($value, FILTER_VALIDATE_URL)
		&& preg_match(self::REGEX, $value)
		&& parse_url($value) !== FALSE);
}

/*=======================================================*/
}
