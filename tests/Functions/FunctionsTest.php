<?php declare(strict_types=1);

namespace Yau\Functions;

use PHPUnit\Framework\TestCase;
use Yau\Functions\Functions;

/**
 * Tests for Yau\Functions\Functions
 */
class FunctionsTest extends TestCase
{
/*=======================================================*/

/**
 */
public function test_array_filter_key()
{
	$actual = Functions::array_filter_key([]);
	$this->assertIsArray($actual);
	$this->assertSame([], $actual);

	$actual = Functions::array_filter_key(['1'=>'James','0'=>'John','2'=>'Joe',''=>'Jack']);
	$this->assertIsArray($actual);
	$this->assertSame(['1'=>'James','2'=>'Joe'], $actual);

	$actual = Functions::array_filter_key(['abc'=>'Jim',1=>'James',0=>'John',2=>'Joe']);
	$this->assertIsArray($actual);
	$this->assertSame(['abc'=>'Jim',1=>'James',2=>'Joe'], $actual);

	// Filter with callback for even keys
	$actual = Functions::array_filter_key([1=>'one',2=>'two',3=>'three',4=>'four'], fn($key) => $key % 2 == 0);
	$this->assertIsArray($actual);
	$this->assertSame([2=>'two',4=>'four'], $actual);
}

/**
*/
public function test_array_rowsort()
{
	$arr = [];
	$arr[] = ['age' => 22, 'fname' => 'John', 'lname' => 'Doe'];
	$arr[] = ['age' => 20, 'fname' => 'Sam',  'lname' => 'Smith'];
	$arr[] = ['age' => 38, 'fname' => 'Phil', 'lname' => 'Jones'];
	$arr[] = ['age' => 31, 'fname' => 'Ted',  'lname' => 'Smith'];
	$arr[] = ['age' => 22, 'fname' => 'Gary', 'lname' => 'Klein'];
	$arr[] = ['age' => 43, 'fname' => 'John', 'lname' => 'Jones'];
	$arr[] = ['age' => 29, 'fname' => 'Bob',  'lname' => 'Davis'];
	$arr[] = ['age' => 37, 'fname' => 'Lee',  'lname' => 'Hall'];

	// Expected output
	$expected = [
		['age' => 29, 'fname' => 'Bob',  'lname' => 'Davis'],
		['age' => 22, 'fname' => 'John', 'lname' => 'Doe'],
 		['age' => 37, 'fname' => 'Lee',  'lname' => 'Hall'],
		['age' => 38, 'fname' => 'Phil', 'lname' => 'Jones'],
		['age' => 43, 'fname' => 'John', 'lname' => 'Jones'],
		['age' => 22, 'fname' => 'Gary', 'lname' => 'Klein'],
		['age' => 20, 'fname' => 'Sam',  'lname' => 'Smith'],
		['age' => 31, 'fname' => 'Ted',  'lname' => 'Smith'],
	];

	// Sort by last name in ascending order, then by age
	$sort_by = [
		'lname', SORT_ASC,
		'age', SORT_ASC, SORT_NUMERIC
	];
	$actual = Functions::array_rowsort($arr, $sort_by);
	$this->assertIsArray($actual);
	$this->assertSame($expected, $actual);

	// Sort by passing multiple arguments instead of a single array
	$actual = Functions::array_rowsort($arr, 'lname', SORT_ASC, 'age', SORT_ASC, SORT_NUMERIC);
	$this->assertIsArray($actual);
	$this->assertSame($expected, $actual);
}

/**
 */
public function test_array_slice_key()
{
	$arr = [
		'fname' => 'John',
		'lname' => 'Doe',
		'age'   => 18,
		'hair'  => 'black'
	];
	$actual = Functions::array_slice_key($arr, ['age', 'hair']);
	$this->assertIsArray($actual);
	$this->assertSame(['age'=>18, 'hair'=>'black'], $actual);

	$actual = Functions::array_slice_key($arr, ['age', 'fname']);
	$this->assertIsArray($actual);
	$this->assertSame(['age'=>18, 'fname'=>'John'], $actual);
}

/**
*/
public function test_array_same_values()
{
	$arr1 = ['blue', 'green', 'red'];
	$arr2 = ['red', 'blue', 'green'];
	$arr3 = ['red', 'blue', 'black'];

	$result = Functions::array_same_values($arr1, $arr2);
	$this->assertTrue($result);

	$result = Functions::array_same_values($arr2, $arr3);
	$this->assertFalse($result);

	$result = Functions::array_same_values($arr1, $arr2, $arr3);
	$this->assertFalse($result);
}

/**
 */
public function test_cidr_match()
{
	$this->assertTrue(Functions::cidr_match('192.168.1.23', '192.168.1.0/24'));
	$this->assertTrue(Functions::cidr_match('1.2.3.4', '0.0.0.0/0'));
	$this->assertTrue(Functions::cidr_match('127.0.0.1', '127.0.0.1/32'));
	$this->assertFalse(Functions::cidr_match('127.0.0.1', '127.0.0.2/32'));
}

/**
 * @return array
 */
public function mathLcmProvider():array
{
	return [
		[[2, 2], 2],
		[[1, 2, 3, 4, 5], 60],
		[[2, 3], 6],
		[[2, 6], 6],
	];
}

/**
 * @param array $numbers
 * @param integer $expected
 * @dataProvider mathLcmProvider
 */
public function test_math_lcm($numbers, $expected)
{
	$this->assertEquals($expected, Functions::math_lcm($numbers));
	$this->assertEquals($expected, call_user_func_array([Functions::class, 'math_lcm'], $numbers));
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


