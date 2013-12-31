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
* An easy wrapper for sorting an array of associative rows
*
* Example
* <code>
* use Yau\Functions\Functions;
*
* $arr = array();
* $arr[] = array('age' => 22, 'fname' => 'John', 'lname' => 'Doe');
* $arr[] = array('age' => 20, 'fname' => 'Sam',  'lname' => 'Smith');
* $arr[] = array('age' => 38, 'fname' => 'Phil', 'lname' => 'Jones');
* $arr[] = array('age' => 31, 'fname' => 'Ted',  'lname' => 'Smith');
* $arr[] = array('age' => 22, 'fname' => 'Gary', 'lname' => 'Klein');
* $arr[] = array('age' => 43, 'fname' => 'John', 'lname' => 'Jones');
* $arr[] = array('age' => 29, 'fname' => 'Bob',  'lname' => 'Davis');
* $arr[] = array('age' => 37, 'fname' => 'Lee',  'lname' => 'Hall');
*
* // Sort by last name in ascending order, then by age
* $sort_by = array(
*      'lname', SORT_ASC,
*      'age', SORT_ASC, SORT_NUMERIC
* );
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
* @param array $arr     the array to sort
* @param array $sort_by array of keys, sort order, and sorting types
* @see   array_multisort()
*/
function array_rowsort(array $arr, $sort_by)
{
	// Array of arguments for array_multisort
	$args = array();

	// Handle usage if arguments aren't passed as an array
	if (!is_array($sort_by))
	{
		$sort_by = func_get_args();
		$arr = array_shift($sort_by);
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
			$values = array();
			foreach ($arr as $rkey => $row)
			{
				$values[$rkey] = array_key_exists($by, $row) ? $row[$by] : NULL;
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

