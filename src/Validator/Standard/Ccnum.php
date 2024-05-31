<?php declare(strict_types = 1);

namespace Yau\Validator\Standard;

use Yau\Singleton\Singleton;
use Yau\Validator\ValidatorInterface;

/**
 * Class to check that a value is a valid credit card number
 *
 * @author John Yau
 */
class Ccnum extends Singleton implements ValidatorInterface
{
/*=======================================================*/

/**
 * An array of regular expressions for some credit cards
 *
 * @var array
 */
private static $CARD_PATTERN = [
	'/^5[1-5]\d{14}$/',                           // mastercard : prefix 51-55;              length of 16
	'/^4\d{12}\d{3}?$/',                          // visa:        prefix 4;                  length of 13 or 16
	'/^3[47]\d{13}$/',                            // amex:        prefix 34 or 37;           length of 15
	'/^6011\d{12}$/',                             // discover:    prefix 6011;               length of 16
	'/^(?:30[0-5]\d{11}|3[68]\d{12})$/',          // diners club: prefix 300-305, 36, or 38; length of 14
	'/^(?:3\d{14,15}|(?:1800|2131)\d{11,12})$/',  // jcb:         prefix 3, 1800, or 2131;   length of 15 or 16
];

/**
 * Check that a value is a valid credit card number
 *
 * @param string $value the value to check
 * @return bool true if check passes, or false if not
 */
public function isValid($value):bool
{
	// Do some basic checking
	$value = filter_var($value, FILTER_SANITIZE_NUMBER_INT);
	$vlength = strlen($value);
	if ($vlength < 13 || $vlength > 16)
	{
		return false;
	}

	// Assign some values
	$card_length = strlen($value);
	$checksum = 0;
	$j = 1;  // Takes value of 1 or 2

	// Go through to calculate
	for ($i = $card_length - 1; $i >= 0; $i--)
	{
		// Extract the next digit and multiply by 1 or 2 on alternative digits
		$calc = $value[$i] * $j;

		// For two digit results, add the individual numbers themselves
		// (which is the equivalent of subtracting by 9)
		if ($calc > 9) $calc -= 9;

		// Add value to checksum
		$checksum += $calc;

		// Switch the value of j
		$j = ($j == 1) ? 2 : 1;
	}

	// If checksum is not divisible by 10, then it's not a valid modulus 10
	if ($checksum % 10 != 0)
	{
		return false;
	}

	// Make sure number validates with least one valid
	foreach (self::$CARD_PATTERN as $pattern)
	{
		if (preg_match($pattern, $value))
		{
			return true;
		}
	}

	// Else return false
	return false;
}

/*=======================================================*/
}
