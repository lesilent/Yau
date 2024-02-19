<?php declare(strict_types = 1);

namespace Yau\Validator\Standard;

use Yau\Singleton\Singleton;
use Yau\Validator\ValidatorInterface;

/**
 * Class to check that a value is a valid US zip code format
 *
 * @author John Yau
 * @link https://github.com/lesilent/Yau
 */
class Zip extends Singleton implements ValidatorInterface
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
 * @return bool TRUE if check passes, or FALSE if not
 */
public function isValid($value):bool
{
	return (bool) preg_match(self::PATTERN, $value);
}

/*=======================================================*/
}
