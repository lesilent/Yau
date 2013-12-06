<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_Functions
*/

namespace Yau\Functions;

use Yau\Functions\Exception\InvalidArgumentException;

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
class Functions
{
/*=======================================================*/

/**
* Load a function and return it's fully-namespaced name
*
* @param  string $func
* @return string
*/
public static function loadFunction($func)
{
	if (!preg_match('/^[a-z_]+$/', $func))
	{
		throw new InvalidArgumentException('Invalid function ' . $func);
	}
	$ns_func = __NAMESPACE__ . '\\' . $func;
	if (!function_exists($ns_func))
	{
		require __DIR__ . DIRECTORY_SEPARATOR . $func . '.php';
	}
	return $ns_func;
}

/**
* Call a static function
*
* @param  string $func
* @param  array  $args
* @return mixed
*/
public static function __callStatic($func, $args)
{
	$func = self::loadFunction($func);
	return call_user_func_array($func, $args);
}

/*=======================================================*/
}
