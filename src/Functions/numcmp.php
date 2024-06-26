<?php declare(strict_types = 1);

namespace Yau\Functions;

/**
 * Function for number comparison
 *
 * This function is sort of like strcmp, but for numbers. This for the most
 * part is no longer needed since spaceship operator was introduced in PHP7.
 *
 * Example
 * <code>
 * use Yau\Functions\Functions;
 *
 * function cmp_asc($a, $b)
 * {
 *     return Functions::numcmp($a, $b);
 * }
 *
 * function cmp_desc($a, $b)
 * {
 *     return Functions::numcmp($b, $a);
 * }
 *
 * $arr = array(13, 3, 9, 10, 1.2, 5);
 * usort($arr, 'cmp_asc');
 * print_r($arr);
 *
 * usort($arr, 'cmp_desc');
 * print_r($arr);
 * </code>
 *
 * @param integer $num1
 * @param integer $num2
 * @return integer returns < 0 if num1 is less than num2; > 0 if num1 is greater
 *                  num2, and 0 if they are equal
 */
function numcmp($num1, $num2)
{
	if ($num1 > $num2)
	{
		return 1;
	}
	elseif ($num1 < $num2)
	{
		return -1;
	}
	else
	{
		return 0;
	}
}
