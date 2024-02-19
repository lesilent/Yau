<?php declare(strict_types = 1);

namespace Yau\Validator\Standard;

use Yau\Singleton\Singleton;
use Yau\Validator\ValidatorInterface;

/**
 * Class to check that a value is a valid US employer identification number (EIN)
 *
 * @author John Yau
 */
class Ein extends Singleton implements ValidatorInterface
{
/*=======================================================*/

/**
 * Regular expression for validating employer identification number
 *
 * @var string
 */
const PATTERN = '/^(\d{2})\-?(\d{7})$/';

/**
 * Check that a value is a valid employer identification number format
 *
 * @param string  $value the value to check
 * @return bool true if check passes, or false if not
 * @link http://en.wikipedia.org/wiki/Employer_Identification_Number#EIN_format
 */
public function isValid($value):bool
{
	return (preg_match(self::PATTERN, $value, $matches) && (
		(strcmp($matches[1], '01') >= 0 && strcmp($matches[1], '06') <= 0)
			|| (strcmp($matches[1], '10') >= 0 && strcmp($matches[1], '16') <= 0)
			|| (strcmp($matches[1], '20') >= 0 && strcmp($matches[1], '27') <= 0)
			|| (strcmp($matches[1], '30') >= 0 && strcmp($matches[1], '48') <= 0)
			|| (strcmp($matches[1], '50') >= 0 && strcmp($matches[1], '68') <= 0)
			|| (strcmp($matches[1], '71') >= 0 && strcmp($matches[1], '77') <= 0)
			|| (strcmp($matches[1], '80') >= 0 && strcmp($matches[1], '88') <= 0)
			|| (strcmp($matches[1], '90') >= 0 && strcmp($matches[1], '95') <= 0)
			|| (strcmp($matches[1], '98') >= 0 && strcmp($matches[1], '99') <= 0)
		));
}

/*=======================================================*/
}
