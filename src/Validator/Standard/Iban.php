<?php declare(strict_types = 1);

namespace Yau\Validator\Standard;

use Yau\Validator\ValidatorInterface;

/**
 * Class to check that a value is a valid International Bank Account Number (IBAN)
 *
 * @author John Yau
 */
class Iban implements ValidatorInterface
{
/*=======================================================*/

/**
 * Regular expression for validating a IBAN number
 *
 * @var string
 */
const PATTERN = '/^([A-Z]{2})(\d{2})((?:\s?[A-Z\d]{4}){0,7}\s?[A-Z\d]{1,4})$/i';

/**
 * Check that a value is a valid IBAN
 *
 * @param mixed $value the value to check
 * @return bool true if check passes, or false if not
 * @link http://en.wikipedia.org/wiki/IBAN
 * @link http://alexandrerodichevski.chiappani.it/doc.php?n=219&lang=en
 */
public function isValid($value): bool
{
	if (preg_match(self::PATTERN, $value, $matches)
		&& ($bban = preg_replace('/\s/', '', $matches[3]))
		&& strlen($bban) <= 30)
	{
		$dec_str = preg_replace_callback('/(.)/', fn($matches) => base_convert($matches[1], 36, 10), $bban . $matches[1] . $matches[2]);
		$remainder = '';
/*
		foreach (str_split($dec_str, 8) as $number)
		{
			$remainder = (int) fmod($remainder . $number, 97);
		}
*/
		foreach (str_split($dec_str, 6) as $number)
		{
			$remainder = intval($remainder . $number) % 97;
		}
		return ($remainder == 1);
	}
	return false;
}

/*=======================================================*/
}
