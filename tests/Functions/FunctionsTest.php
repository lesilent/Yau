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

 	$a = \Yau\Functions\array_slice_key(array(),array());
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


