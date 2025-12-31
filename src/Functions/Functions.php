<?php declare(strict_types = 1);

namespace Yau\Functions;

use InvalidArgumentException;

/**
 * Extract a slice of an array based on its keys
 *
 * Example:
 * <code>
 * use Yau\Functions\Functions;
 *
 * $arr = [
 *     'fnmae' => 'John',
 *     'lname' => 'Doe',
 *     'age'   => 18,
 *     'hair'  => 'black'
 * ];
 * $input = Functions::array_slice_key($arr, ['age', 'hair']);
 * // $input is now ['age'=>18, 'hair'=>'black'];
 * </code>
 *
 * @method static array array_filter_key(array $input, $callback = null)
 * @method static array array_rowsort(array $arr, ...$sort_by)
 * @method static array array_slice_key(array $arr, array $keys)
 * @method static bool array_same_values(array $array, array ...$arrays)
 * @method static bool cidr_match(string $ip, string $mask)
 * @method static int|false math_lcm(...$numbers)
 * @method static int numcmp($num1, $num2)
 * @method static string|false temp_filename(string $prefix = 'yau', ?string $directory = null)
 */
class Functions
{
/*=======================================================*/

/**
 * Load a function and return it's fully-namespaced name
 *
 * @param string $func
 * @return string
 */
public static function loadFunction(string $func)
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
 * @param string $func
 * @param array $args
 * @return mixed
 */
public static function __callStatic(string $func, array $args)
{
	$func = self::loadFunction($func);
	return call_user_func_array($func, $args);
}

/*=======================================================*/
}
