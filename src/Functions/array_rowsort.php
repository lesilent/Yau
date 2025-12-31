<?php declare(strict_types = 1);

namespace Yau\Functions;

/**
 * An easy wrapper for sorting an array of associative rows
 *
 * Example
 * <code>
 * use Yau\Functions\Functions;
 *
 * $arr = [];
 * $arr[] = ['age' => 22, 'fname' => 'John', 'lname' => 'Doe'];
 * $arr[] = ['age' => 20, 'fname' => 'Sam',  'lname' => 'Smith'];
 * $arr[] = ['age' => 38, 'fname' => 'Phil', 'lname' => 'Jones'];
 * $arr[] = ['age' => 31, 'fname' => 'Ted',  'lname' => 'Smith'];
 * $arr[] = ['age' => 22, 'fname' => 'Gary', 'lname' => 'Klein'];
 * $arr[] = ['age' => 43, 'fname' => 'John', 'lname' => 'Jones'];
 * $arr[] = ['age' => 29, 'fname' => 'Bob',  'lname' => 'Davis'];
 * $arr[] = ['age' => 37, 'fname' => 'Lee',  'lname' => 'Hall'];
 *
 * // Sort by last name in ascending order, then by age
 * $sort_by = [
 *      'lname', SORT_ASC,
 *      'age', SORT_ASC, SORT_NUMERIC
 * ];
 *
 * $arr = Functions::array_rowsort($arr, $sort_by);
 * </code>
 *
 * Output from the above example:
 * <pre>
 * array (
 *   0 =>
 *   array (
 *     'age' => 29,
 *     'fname' => 'Bob',
 *    'lname' => 'Davis',
 *   ),
 *   1 =>
 *   array (
 *     'age' => 22,
 *     'fname' => 'John',
 *     'lname' => 'Doe',
 *   ),
 *   2 =>
 *   array (
 *     'age' => 37,
 *     'fname' => 'Lee',
 *     'lname' => 'Hall',
 *   ),
 *   3 =>
 *   array (
 *     'age' => 38,
 *     'fname' => 'Phil',
 *     'lname' => 'Jones',
 *  ),
 *   4 =>
 *   array (
 *     'age' => 43,
 *     'fname' => 'John',
 *     'lname' => 'Jones',
 *   ),
 *   5 =>
 *   array (
 *     'age' => 22,
 *     'fname' => 'Gary',
 *     'lname' => 'Klein',
 *   ),
 *   6 =>
 *   array (
 *     'age' => 20,
 *     'fname' => 'Sam',
 *     'lname' => 'Smith',
 *   ),
 *   7 =>
 *   array (
 *     'age' => 31,
 *     'fname' => 'Ted',
 *     'lname' => 'Smith',
 *   ),
 * )
 * </pre>
 *
 * List of sorting options:
 * <ul>
 * <li>SORT_ASC
 * <li>SORT_DESC
 * <li>SORT_REGULAR
 * <li>SORT_NUMERIC
 * <li>SORT_STRING
 * </ul>
 *
 * @param array $arr the array to sort
 * @param array $sort_by array of keys, sort order, and sorting types
 * @return array
 * @see array_multisort()
 */
function array_rowsort(array $arr, ...$sort_by)
{
	// Array of arguments for array_multisort
	$args = [];

	// Handle usage if arguments are passed as an array
	if (!empty($sort_by) && is_array($sort_by[0]))
	{
		$sort_by = reset($sort_by);
	}

	// Process sort by parameter
	foreach ($sort_by as $by)
	{
		if (is_numeric($by))
		{
			// If numeric, then it's a sort type/order flag
			$args[] = $by;
		}
		else
		{
			// Else obtain a list of column values for key
			$values = [];
			foreach ($arr as $rkey => $row)
			{
				$values[$rkey] = array_key_exists($by, $row) ? $row[$by] : null;
			}
			$args[] = $values;
		}
	}

	// Add the array as the last parameter
	$args[] =& $arr;

	// Sort the array
	call_user_func_array('array_multisort', $args);
	return $arr;
}
