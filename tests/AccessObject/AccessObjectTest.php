<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Yau\AccessObject\AccessObject;

/**
* Tests for Yau\AccessObject\AccessObject
*/
class AccessObjectTest extends TestCase
{
/*=======================================================*/

/**
 */
public function testGetterSetter():void
{
	$obj = new AccessObject();

	$obj->test1 = 12345;
	$this->assertSame(12345, $obj->test1);
	$this->assertSame(12345, $obj['test1']);

	$obj['test2'] = 12345;
	$this->assertSame(12345, $obj['test2']);
	$this->assertSame(12345, $obj->test2);

	$obj->assign(['test3'=>12345]);
	$this->assertSame(12345, $obj->test3);
	$this->assertSame(12345, $obj['test3']);
}

/**
 */
public function testAssign():void
{
	$obj1 = new AccessObject();
	$obj1->test1 = 12345;
	$obj2 = new AccessObject();
	$obj2->assign($obj1);
	$obj2->test2 = 12345;
	$this->assertEquals(12345, $obj2->test1);
	$this->assertEquals(12345, $obj2->test2);
}

/**
 */
public function testUndefinedValueException():void
{
	$this->expectException(InvalidArgumentException::class);
	$obj = new AccessObject();
	$obj->setUndefinedValue(true);
}

/**
 */
public function testUndefinedValue():void
{
	$obj = new AccessObject();
	$obj->setUndefinedValue(false);
	$this->assertSame(false, $obj->undefkey);
}

/**
 */
public function testInternalRegistry():void
{
	$obj = new AccessObject();
	$obj->test1 = 12345;
	$obj->registry = [];
	$obj->_registry = [];
	$this->assertEquals(12345, $obj->test1);
}

/**
 */
public function testSorters():void
{
	$obj = new AccessObject();

	$obj->assign([
		'file1' => 'img12.png',
		'file2' => 'img10.png',
		'file3' => 'img2.png',
		'file4' => 'img1.png',
	]);
	$obj->natsort();
	$this->assertEquals('img1.png img2.png img10.png img12.png', implode(' ', $obj->toArray()));

	$obj->assign([
		'file1' => 'IMG0.png',
		'file2' => 'img12.png',
		'file3' => 'img10.png',
		'file4' => 'img2.png',
		'file5' => 'img1.png',
		'file6' => 'IMG3.png',
	]);
	$obj->natcasesort();
	$this->assertEquals('IMG0.png img1.png img2.png IMG3.png img10.png img12.png', implode(' ', $obj->toArray()));

	$obj->ksort();
	$this->assertEquals('file1file2file3file4file5file6', implode('', array_keys($obj->toArray())));
}

/**
*/
public function testCountable():void
{
	$obj = new AccessObject();
	$obj->test = 12345;
	$obj->clear();
	$this->assertCount(0, $obj);
	$obj->test1 = 12345;
	$obj->test2 = 12345;
	$obj->test3 = 12345;
	$this->assertCount(3, $obj);
}

/**
*/
public function testIterator():void
{
	$obj = new AccessObject();
	$obj->test1 = 1;
	$obj->test2 = 2;
	$obj->test3 = 3;
	$names = '';
	$total = 0;
	foreach ($obj as $name => $value)
	{
		$names .= $name;
		$total += $value;
	}
	$this->assertEquals('test1test2test3', $names);
	$this->assertEquals(6, $total);
}

/**
 */
public function testSerializable():void
{
	$obj = new AccessObject();
	$obj->fname = 'John';
	$obj['lname'] = 'Doe';
	$s = serialize($obj);
	$this->assertTrue(is_string($s));
	unset($obj);
	$this->assertTrue(!isset($obj));
	$obj = unserialize($s);
	$this->assertInstanceOf(AccessObject::class, $obj);
	$this->assertEquals('John', $obj->fname);
	$this->assertEquals('Doe', $obj->lname);
}

/*=======================================================*/
}
