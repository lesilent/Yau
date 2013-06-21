<?php

/**
*
* @category Yau
* @package  Yau_ClassLoader
*/

namespace YauTest\ClassLoader;

use Yau\ClassLoader\ClassLoader;

/**
*
* @category Yau
* @package  Yau_ClassLoader
*/
class ClassLoaderTest extends \PHPUnit_Framework_TestCase
{
/*=======================================================*/

/**
* @var object
*/
private $loader;

/**
*/
public function setUp()
{
	$this->loader = new ClassLoader();
	$this->loader->registerNamespace('YauTest', __DIR__);
	$this->loader->registerPrefix('YauTest_', __DIR__);
}

/**
* @return array
*/
public function providerClasses()
{
	return array(
		// Namespace classes
		array(__DIR__ . DIRECTORY_SEPARATOR . 'TestClass.php', 'YauTest\\TestClass'),
		array(__DIR__ . DIRECTORY_SEPARATOR . 'Module' . DIRECTORY_SEPARATOR . 'TestClass.php', 'YauTest\\Module\\TestClass'),
		array(__DIR__ . DIRECTORY_SEPARATOR . 'Module' . DIRECTORY_SEPARATOR . 'SubModule' . DIRECTORY_SEPARATOR . 'TestClass.php', 'YauTest\\Module\\SubModule\\TestClass'),

		// Prefix classes
		array(__DIR__ . DIRECTORY_SEPARATOR . 'TestClass.php', 'YauTest_TestClass'),
		array(__DIR__ . DIRECTORY_SEPARATOR . 'Module' . DIRECTORY_SEPARATOR . 'TestClass.php', 'YauTest_Module_TestClass'),
		array(__DIR__ . DIRECTORY_SEPARATOR . 'Module' . DIRECTORY_SEPARATOR . 'SubModule' . DIRECTORY_SEPARATOR . 'TestClass.php', 'YauTest_Module_SubModule_TestClass'),

		// Namespace and underscores
		array(__DIR__ . DIRECTORY_SEPARATOR . 'Module' . DIRECTORY_SEPARATOR . 'Test' . DIRECTORY_SEPARATOR . 'SubClass.php', 'YauTest\\Module\Test_SubClass'),
		array(__DIR__ . DIRECTORY_SEPARATOR . 'Sub_Module' . DIRECTORY_SEPARATOR . 'Test' . DIRECTORY_SEPARATOR . 'SubClass.php', 'YauTest\\Sub_Module\Test_SubClass'),

		// Invalid classes
		array(FALSE, 'Yau\Test'),
		array(FALSE, 'Yau_Test'),
		array(FALSE, 'YauTesting'),
	);
}

/**
* @param        string $path
* @param        string $class_name
* @dataProvider providerClasses
*/
public function testClassPath($path, $class_name)
{
	$this->assertSame($path, $this->loader->getPath($class_name));
	$this->assertFalse($this->loader->classExists($class_name));
}

/**
*/
public function testRegister()
{
	$this->assertTrue($this->loader->register());
	$this->assertFalse($this->loader->register());
	$this->assertTrue($this->loader->unregister());
	$this->assertFalse($this->loader->unregister());
}

/*=======================================================*/
}
