<?php declare(strict_types=1);

namespace Yau\IncludePath;

use PHPUnit\Framework\TestCase;
use Yau\IncludePath\IncludePath;
use Yau\IncludePath\Paths;

/**
 * Tests for Yau\IncludePath
 */
class IncludePathTest extends TestCase
{
/*=======================================================*/

/**
 * Test IncludePath static functions
 */
public function testIncludePath(): void
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

/**
 * Test Paths instance
 */
public function testPaths()
{
	$paths = new Paths([], true);
	$this->assertSame(get_include_path(), $paths->__toString());
	$paths->addPath(__DIR__);
	$this->assertSame(get_include_path() . PATH_SEPARATOR . __DIR__, $paths->__toString());

	$paths = new Paths(__DIR__);
	$this->assertSame(__DIR__, $paths->__toString());
}

/*=======================================================*/
}
