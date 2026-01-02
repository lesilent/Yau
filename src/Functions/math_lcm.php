<?php declare(strict_types = 1);

namespace Yau\Functions;

use InvalidArgumentException;

/**
 * Function for returning the least common multiple among an array of integers
 *
 * Example
 * <code>
 * use Yau\Functions\Functions;
 *
 * // Using arrays
 * $numbers = [1, 2, 3, 4, 5];
 * echo Functions::math_lcm($numbers);   // Outputs 60
 *
 * // Using multiple arguments
 * echo Functions::math_lcm(1, 2, 3, 4, 5);   // Outputs 60
 * </code>
 *
 * @param array $numbers
 * @return int|false the least common multiple
 */
function math_lcm(...$numbers)
{
	// Check numbers
	if (empty($numbers))
	{
		trigger_error('Array must contain at least one element to find least common multiple');
		return false;
	}

	// Get all numbers
	$all_numbers = [];
	foreach ($numbers as $value)
	{
		if (!is_array($value))
		{
			$value = [$value];
		}
		$all_numbers = [...$all_numbers, ...$value];
	}
	if (min($all_numbers) < 1)
	{
		trigger_error('Cannot find least common multiple of numbers less than 1');
		return false;
	}

	// Find lowest common denominator
	$result = $largest_number = max($all_numbers);
	reset($all_numbers);
	while ($num = current($all_numbers))
	{
		if ($result % $num == 0)
		{
			next($all_numbers);
		}
		else
		{
			$result += $largest_number;
			reset($all_numbers);
		}
	}

	// Return result
	return $result;
}
