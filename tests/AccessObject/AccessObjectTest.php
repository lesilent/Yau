<?php

/**
*
* @category Yau
* @package  Yau_AccessObject
*/

namespace YauTest\AccessObject;

use Yau\AccessObject\AccessObject;

/**
*
* @category Yau
* @package  Yau_MDBAC
*/
class AccessObjectTest extends \PHPUnit_Framework_TestCase
{
/*=======================================================*/

private $obj;

public function __construct()
{
	$this->obj = new AccessObject();
}

/**
*/
public function testGetterSetter()
{
	$this->obj->test1 = 12345;
	$this->assertSame(12345, $this->obj->test1);
	$this->assertSame(12345, $this->obj['test1']);

	$this->obj['test2'] = 12345;
	$this->assertSame(12345, $this->obj['test2']);
	$this->assertSame(12345, $this->obj->test2);

	$this->obj->assign(array('test3'=>12345));
	$this->assertSame(12345, $this->obj->test3);
	$this->assertSame(12345, $this->obj['test3']);
}

/**
*/
public function testAssign()
{
	$obj = new AccessObject();
	$obj->test1 = 12345;
	$this->obj->clear();
	$this->obj->assign($obj);
	$this->obj->test2 = 12345;
	$this->assertEquals(12345, $this->obj->test1);
	$this->assertEquals(12345, $this->obj->test2);
}

/**
* @expectedException Yau\AccessObject\Exception\InvalidArgumentException
*/
public function testUndefinedValueException()
{
	$this->obj->setUndefinedValue(TRUE);
}

/**
*/
public function testUndefinedValue()
{
	$this->obj->setUndefinedValue(FALSE);
	$this->assertSame(FALSE, $this->obj->undefkey);
}

/**
*/
public function testInternalRegistry()
{
	$this->obj->test1 = 12345;
	$this->obj->registry = array();
	$this->obj->_registry = array();
	$this->assertEquals(12345, $this->obj->test1);
}

/**
*/
public function testSorters()
{
	$this->obj->clear();
	$this->obj->assign(array(
		'file1' => 'img12.png',
		'file2' => 'img10.png',
		'file3' => 'img2.png',
		'file4' => 'img1.png',
	));

	$this->obj->natsort();
	$this->assertEquals('img1.png img2.png img10.png img12.png', implode(' ', $this->obj->toArray()));

	$this->obj->assign(array(
		'file1' => 'IMG0.png',
		'file2' => 'img12.png',
		'file3' => 'img10.png',
		'file4' => 'img2.png',
		'file5' => 'img1.png',
		'file6' => 'IMG3.png',
	));
	$this->obj->natcasesort();
	$this->assertEquals('IMG0.png img1.png img2.png IMG3.png img10.png img12.png', implode(' ', $this->obj->toArray()));

	$this->obj->ksort();
	$this->assertEquals('file1file2file3file4file5file6', implode('', array_keys($this->obj->toArray())));

}

/**
*/
public function testCountable()
{
	$this->obj->test = 12345;
	$this->obj->clear();
	$this->assertCount(0, $this->obj);
	$this->obj->test1 = 12345;
	$this->obj->test2 = 12345;
	$this->obj->test3 = 12345;
	$this->assertCount(3, $this->obj);
}

/**
*/
public function testIterator()
{
	$this->obj->clear();
	$this->obj->test1 = 1;
	$this->obj->test2 = 2;
	$this->obj->test3 = 3;
	$names = '';
	$total = 0;
	foreach ($this->obj as $name => $value)
	{
		$names .= $name;
		$total += $value;
	}
	$this->assertEquals('test1test2test3', $names);
	$this->assertEquals(6, $total);
}

/**
*/
public function testSerializable()
{
	$obj = new AccessObject();
	$obj->fname = 'John';
	$obj['lname'] = 'Doe';
	$s = serialize($obj);
	$this->assertTrue(is_string($s));
	unset($obj);
	$this->assertTrue(!isset($obj));
	$obj = unserialize($s);
	$this->assertInstanceOf('Yau\AccessObject\AccessObject', $obj);
	$this->assertEquals('John', $obj->fname);
	$this->assertEquals('Doe', $obj->lname);
}

/*=======================================================*/
}
