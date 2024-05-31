<?php declare(strict_types = 1);

namespace Yau\Validator\Standard;

use Yau\Singleton\Singleton;
use Yau\Validator\ValidatorInterface;

/**
 * Class to check that a value is a valid time in "HH:MM:SS" format
 *
 * @author John Yau
 */
class Time extends Singleton implements ValidatorInterface
{
/*=======================================================*/

/**
 * Regular expression for validating the time
 *
 * @var string
 */
const PATTERN = '/^([01]\d|2[0-3]):([0-5]\d):([0-5]\d)$/';

/**
* Check that a value is a valid time in HH:MM:SS format
*
* @param string $value the value to check
* @return bool true if check passes, or false if not
*/
public function isValid($value):bool
{
	return (bool) preg_match(self::PATTERN, $value, $match);
}

/*=======================================================*/
}
