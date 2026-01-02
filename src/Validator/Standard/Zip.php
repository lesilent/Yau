<?php declare(strict_types = 1);

namespace Yau\Validator\Standard;

use Yau\Validator\ValidatorInterface;

/**
 * Class to check that a value is a valid US zip code format
 *
 * @author John Yau
 */
class Zip implements ValidatorInterface
{
/*=======================================================*/

/**
 * Regular expression for validating US zip code
 *
 * @var string
 */
const PATTERN = '/^(\d{5})(?:\-(\d{4}))?$/';

/**
 * Check that a value is a valid US zip code
 *
 * @param string $value the value to check
 * @return bool true if check passes, or false if not
 */
public function isValid($value): bool
{
	return (bool) preg_match(self::PATTERN, $value);
}

/*=======================================================*/
}
