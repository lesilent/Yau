<?php declare(strict_types = 1);

namespace Yau\Functions;

/**
 * Extract a slice of an array based on its keys
 *
 * Example:
 * <code>
 * use Yau\Functions\Functions;
 *
 * $arr = [
 *     'fname' => 'John',
 *     'lname' => 'Doe',
 *     'age'   => 18,
 *     'hair'  => 'black'
 * ];
 * $input = Functions::array_slice_key($arr, ['age', 'hair']);
 * // $input is now ['age'=>18, 'hair'=>'black'];
 * </code>
 *
 * @param array $arr the associative array
 * @param array $keys the array of keys to extract from array
 * @return array a slice of the array
 */
function array_slice_key(array $arr, array $keys): array
{
	$result = [];
	foreach (array_intersect($keys, array_keys($arr)) as $k)
	{
		$result[$k] = $arr[$k];
	}
	return $result;
}
