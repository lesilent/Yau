<?php declare(strict_types = 1);

namespace Yau\Validator\Standard;

use Yau\Validator\ValidatorInterface;

/**
 * Class to check that a value is a valid ip address
 *
 * @author John Yau
 */
class Ip implements ValidatorInterface
{
/*=======================================================*/

/**
 * Regular expression for validating a ipv4 address
 *
 * @var string
 */
const PATTERN = '/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/';

/**
 * Check that a value is a valid ip address
 *
 * @param mixed $value the value to check
 * @return bool true if check passes, or false if not
 */
public function isValid($value): bool
{
	return (preg_match(self::PATTERN, $value, $match)
		&& strcmp($match[1], '256') < 0
		&& strcmp($match[2], '256') < 0
		&& strcmp($match[3], '256') < 0
		&& strcmp($match[4], '256') < 0);
}

/*=======================================================*/
}
