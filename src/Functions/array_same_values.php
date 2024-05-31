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
* Return whether arrays have the same values
*
* Example:
* <code>
* use Yau\Functions\Functions;
*
* $arr1 = array('blue', 'green', 'red');
* $arr2 = array('red', 'blue', 'green');
* $arr3 = array('red', 'blue', 'black');
*
* // Should be TRUE
* $result = Functions::array_same_values($arr1, $arr2);
*
* // Should be FALSE
* $result = Functions::array_same_values($arr2, $arr3);
* </code>
*
* @param  array   $array1
* @param  array   $array2
* @return boolean
*/
function array_same_values(array $array1, array $array2)
{
	$argc = func_num_args();
	$arr_count = count($array1);
	for ($i = 1; $i < $argc; $i++)
	{
		$arr = func_get_arg($i);
		if (count($arr) != $arr_count || array_diff($arr, $array1))
		{
			return FALSE;
		}
	}
	return TRUE;
}
