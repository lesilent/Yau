<?php declare(strict_types=1);

namespace Yau\Functions;

/**
 * Return whether arrays have the same values
 *
 * Example:
 * <code>
 * use Yau\Functions\Functions;
 *
 * $arr1 = ['blue', 'green', 'red'];
 * $arr2 = ['red', 'blue', 'green'];
 * $arr3 = ['red', 'blue', 'black'];
 *
 * // Should be true
 * $result = Functions::array_same_values($arr1, $arr2);
 *
 * // Should be false
 * $result = Functions::array_same_values($arr2, $arr3);
 * </code>
 *
 * @param array $array
 * @param array $arrays
 * @return bool
 */
function array_same_values(array $array, array ...$arrays): bool
{
	$arr_count = count($array);
	foreach ($arrays as $arr)
	{
		if (count($arr) != $arr_count || array_diff($arr, $array))
		{
			return false;
		}
	}
	return true;
}
