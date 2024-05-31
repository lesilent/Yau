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
* Extract a slice of an array based on its keys
*
* Example:
* <code>
* use Yau\Functions\Functions;
*
* $arr = array(
*     'fnmae' => 'John',
*     'lname' => 'Doe',
*     'age'   => 18,
*     'hair'  => 'black'
* );
* $input = Functions::array_slice_key($arr, array('age', 'hair'));
* // $input is now array('age'=>18, 'hair'=>'black');
* </code>
*
* @param  array $arr  the associative array
* @param  array $keys the array of keys to extract from array
* @return array a slice of the array
*/
function array_slice_key(array $arr, array $keys)
{
	$result = array();
	foreach (array_intersect($keys, array_keys($arr)) as $k)
	{
		$result[$k] = $arr[$k];
	}
	return $result;
}
