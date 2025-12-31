<?php declare(strict_types = 1);

namespace Yau\Validator\Standard;

use Yau\Singleton\Singleton;
use Yau\Validator\ValidatorInterface;

/**
 * Class to check that value is valid date time in "YYYY-MM-DD HH:MM:SS" format
 *
 * @author John Yau
 * @link https://github.com/lesilent/Yau
 */
class Datetime extends Singleton implements ValidatorInterface
{
/*=======================================================*/

/**
 * Regular expression for validating the date time
 *
 * @var string
 */
const PATTERN = '/^(\d{4})\-(0\d|1[0-2])\-([0-2]\d|3[01])\s([01]\d|2[0-3]):([0-5]\d):([0-5]\d)$/';

/**
 * Check that a value is a valid date in YYYY-MM-DD HH:MM:SS format
 *
 * @param string $value the value to check
 * @return bool true if check passes, or false if not
 * @uses checkdate()
 */
public function isValid($value): bool
{
	return (preg_match(self::PATTERN, $value, $matches)
		&& checkdate((int) $matches[2], (int) $matches[3], (int) $matches[1]));
}

/*=======================================================*/
}
