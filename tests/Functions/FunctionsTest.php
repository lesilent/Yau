<?php

/**
* Yau Tools Tests
*
* @author  John Yau
*/
namespace YauTest\Functions;

use Yau\Functions\Functions;
use Yau\Functions AS Func;

/**
*
*/
class FunctionsTest extends \PHPUnit_Framework_TestCase
{
/*=======================================================*/

/**
*/
public function test_array_filter_key()
{
	$output = Functions::array_filter_key(array());
	$this->assertSame($output, array());

	$arr = array(
		'1' => 'James',
		'0' => 'John',
		'2' => 'Joe',
		''  => 'Jack',
	);
	$output = Functions::array_filter_key($arr);
	$this->assertSame($output, array('1'=>'James', '2'=>'Joe'));

	$arr = array(
		'abc' => 'Jim',
		1     => 'James',
		0     => 'John',
		2     => 'Joe',
		false => 'Jack',
		true  => 'Jake',
	);
	$output = Functions::array_filter_key($arr);
	$this->assertSame($output, array('abc'=>'Jim', 1=>'James', 2=>'Joe', true=>'Jake'));
}

/**
*/
public function test_array_rowsort()
{
	$arr = array();
	$arr[] = array('age' => 22, 'fname' => 'John', 'lname' => 'Doe');
	$arr[] = array('age' => 20, 'fname' => 'Sam',  'lname' => 'Smith');
	$arr[] = array('age' => 38, 'fname' => 'Phil', 'lname' => 'Jones');
	$arr[] = array('age' => 31, 'fname' => 'Ted',  'lname' => 'Smith');
	$arr[] = array('age' => 22, 'fname' => 'Gary', 'lname' => 'Klein');
	$arr[] = array('age' => 43, 'fname' => 'John', 'lname' => 'Jones');
	$arr[] = array('age' => 29, 'fname' => 'Bob',  'lname' => 'Davis');
	$arr[] = array('age' => 37, 'fname' => 'Lee',  'lname' => 'Hall');

	// Expected output
	$expected = array(
		array('age' => 29, 'fname' => 'Bob',  'lname' => 'Davis'),
		array('age' => 22, 'fname' => 'John', 'lname' => 'Doe'),
 		array('age' => 37, 'fname' => 'Lee',  'lname' => 'Hall'),
		array('age' => 38, 'fname' => 'Phil', 'lname' => 'Jones'),
		array('age' => 43, 'fname' => 'John', 'lname' => 'Jones'),
		array('age' => 22, 'fname' => 'Gary', 'lname' => 'Klein'),
		array('age' => 20, 'fname' => 'Sam',  'lname' => 'Smith'),
		array('age' => 31, 'fname' => 'Ted',  'lname' => 'Smith'),
	);

	// Sort by last name in ascending order, then by age
	$sort_by = array(
		'lname', SORT_ASC,
		'age', SORT_ASC, SORT_NUMERIC
	);
	$output = Functions::array_rowsort($arr, $sort_by);
	$this->assertSame($output, $expected);

	// Sort by passing multiple arguments instead of a single array
	$output = Functions::array_rowsort($arr, 'lname', SORT_ASC, 'age', SORT_ASC, SORT_NUMERIC);
	$this->assertSame($output, $expected);
}

/**
*/
public function test_array_slice_key()
{
	$arr = array(
		'fname' => 'John',
		'lname' => 'Doe',
		'age'   => 18,
		'hair'  => 'black'
	);
	$output = Functions::array_slice_key($arr, array('age', 'hair'));
 	$this->assertSame($output, array('age'=>18, 'hair'=>'black'));
}

/**
*/
public function test_array_same_values()
{
	$arr1 = array('blue', 'green', 'red');
	$arr2 = array('red', 'blue', 'green');
	$arr3 = array('red', 'blue', 'black');

	$result = Functions::array_same_values($arr1, $arr2);
	$this->assertTrue($result);

	$result = Functions::array_same_values($arr2, $arr3);
	$this->assertFalse($result);
}

/**
*/
public function test_cidr_match()
{
	$this->assertTrue(Functions::cidr_match('192.168.1.23', '192.168.1.0/24'));
}

/**
*/
public function test_math_lcm()
{
	$this->assertEquals(60, Functions::math_lcm(array(1, 2, 3, 4, 5)));
	$this->assertEquals(60, Functions::math_lcm(1, 2, 3, 4, 5));
}

/**
*/
public function test_numcmp()
{
	$this->assertGreaterThan(0, Functions::numcmp(2, 1));
	$this->assertEquals(0, Functions::numcmp(3, 3));
	$this->assertLessThan(0, Functions::numcmp(1, 3));
}

/*=======================================================*/
}


