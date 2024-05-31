<?php declare(strict_types = 1);

namespace Yau\Validator\Standard;

use Yau\Singleton\Singleton;
use Yau\Validator\ValidatorInterface;

/**
* Class to check that a value is a valid Individual Taxpayer Identification Number (ITIN)
*
* @author John Yau
* @link http://www.irs.gov/Individuals/General-ITIN-Information
*/
class Itin extends Singleton implements ValidatorInterface
{
/*=======================================================*/

/**
 * Regular expression for validating individual taxpayer identification number
 *
 * @var string
 */
const PATTERN = '/^(9\d\d)\-?([78]\d)\-?(\d{4})$/';

/**
 * Check that a value is a valid social security number format
 *
 * @param string $value the value to check
 * @return bool true if check passes, or false if not
 */
public function isValid($value):bool
{
	return (bool) preg_match(self::PATTERN, $value);
}

/*=======================================================*/
}
