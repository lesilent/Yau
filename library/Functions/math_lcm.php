<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_Functions
*/

namespace Yau\Functions;

/**
* Function for returning the least common multiple among an array of integers
*
* Example
* <code>
* use Yau\Functions\Functions;
*
* // Using arrays
* $numbers = array(1, 2, 3, 4, 5);
* echo Functions::math_lcm($numbers);   // Outputs 60
*
* // Using multiple arguments
* echo Functions::math_lcm(1, 2, 3, 4, 5);   // Outputs 60
* </code>
*
* @param  array   $numbers
* @return integer the least common multiple
*/
function math_lcm($numbers)
{
	// Check numbers
	if (empty($numbers))
	{
		trigger_error('Array must contain at least one element to find least common multiple');
		return FALSE;
	}
	if (!is_array($numbers))
	{
		$numbers = func_get_args();
	}
	if (min($numbers) < 1)
	{
		trigger_error('Cannot find least common multiple of numbers less than 1');
		return FALSE;
	}

	// Initialize some variables
	$number_count = count($numbers);
	$result = $largest_number = max($numbers);

	// Find lowest common denominator
	while ($num = current($numbers))
	{
		if ($result % $num == 0)
		{
			next($numbers);
		}
		else
		{
			$result += $largest_number;
			reset($numbers);
		}
	}

	// Return result
	return $result;
}
