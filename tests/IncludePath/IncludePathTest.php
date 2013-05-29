<?php

/**
* Yau Tools Tests
*
* @author John Yau
*/
namespace YauTest\IncludePath;

use Yau\IncludePath\IncludePath;

/**
*
* @author John Yau
*/
class IncludePathTest extends \PHPUnit_Framework_TestCase
{
/*=======================================================*/

/**
*
*/
public function testStaticFunctions()
{
	$orig_incpath = get_include_path();
	IncludePath::add(__DIR__);
	$incpath = $orig_incpath . PATH_SEPARATOR . __DIR__;
	$this->assertSame(get_include_path(), $incpath);
	IncludePath::add(__DIR__);
	$this->assertSame(get_include_path(), $incpath);
	IncludePath::remove(__DIR__);
	$this->assertSame(get_include_path(), $orig_incpath);
}

public function testInstance()
{
	$incpath = new IncludePath();
	$this->assertSame(get_include_path(), $incpath->__toString());

}

/*=======================================================*/
}
