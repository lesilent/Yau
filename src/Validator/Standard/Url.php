<?php declare(strict_types = 1);

namespace Yau\Validator\Standard;

use Yau\Singleton\Singleton;
use Yau\Validator\ValidatorInterface;

/**
 * Class to check that a value is a valid url
 *
 * Note: this currently only checks HTTP urls
 *
 * @author John Yau
 */
class Url extends Singleton implements ValidatorInterface
{
/*=======================================================*/

/**
 * Regular expression for validating a url format
 *
 * @var string
 */
const PATTERN = '/^https?:\/\/[\w\-]+\.\w+/i';

/**
 * Check that a value is a valid url
 *
 * @param mixed $value the value to check
 * @return bool true if check passes, or false if not
 */
public function isValid($value): bool
{
	return (filter_var($value, FILTER_VALIDATE_URL)
		&& preg_match(self::PATTERN, $value)
		&& parse_url($value) !== false);
}

/*=======================================================*/
}
