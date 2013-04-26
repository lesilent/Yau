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
* Class to check that a value is a valid International Bank Account Number (IBAN)
*
* @author   John Yau
* @category Yau
* @package  Yau_Validator
*/
class Iban extends Singleton implements ValidatorInterface
{
/*=======================================================*/

/**
* Regular expression for validating a IBAN number
*
* @var string
*/
const REGEX = '/^([A-Z]{2})(\d{2})([A-Z\d]{1,30})$/i';

/**
* Check that a value is a valid IBAN
*
* @param  mixed   $value the value to check
* @return boolean TRUE if check passes, or FALSE if not
* @link   http://en.wikipedia.org/wiki/IBAN
* @link   http://alexandrerodichevski.chiappani.it/doc.php?n=219&lang=en
*/
public function isValid($value)
{
	if (preg_match(self::REGEX, $value, $match))
	{
		$dec_str = preg_replace('/(.)/e',
			"base_convert('$1', 36, 10)",
			$match[3] . $match[1] . $match[2]);

		$remainder = '';
/*
		foreach (str_split($dec_str, 8) as $number)
		{
			$remainder = (int) fmod($remainder . $number, 97);
		}
*/
		foreach (str_split($dec_str, 6) as $number)
		{
			$remainder = ($remainder . $number) % 97;
		}
		return ($remainder == 1);
	}
	return FALSE;
}

/*=======================================================*/
}
