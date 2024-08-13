<?php declare(strict_types=1);

namespace Yau\ClassLoader;

use PHPUnit\Framework\TestCase;
use Yau\ClassLoader\ClassLoader;
use Yau\Validator\StandardValidator;

/**
 * This test can be run directly outside of phpunit to test ClassLoader
 */
if (!class_exists('PHPUnit\Framework\TestCase'))
{
	$path = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src';
	if (!class_exists('Yau\ClassLoader\ClassLoader', false))
	{
		require_once $path . DIRECTORY_SEPARATOR . 'ClassLoader' . DIRECTORY_SEPARATOR . 'ClassLoader.php';
	}
	$loader = new \Yau\ClassLoader\ClassLoader();
	$loader->registerNamespace('Yau', $path);
	$email = 'good@email.com';
	$validator = StandardValidator::getInstance();
	$is_valid = $validator->isValidEmail($email);
	assert($is_valid);
	return;
}

/**
 * Tests for Yau\ClassLoader\ClassLoader
 */
class ClassLoaderTest extends TestCase
{

/**
 * @var object
 */
private $loader;

/**
 */
public static function setUpBeforeClass():void
{
	$loader = new ClassLoader();
	$loader->registerNamespace('Yau', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'src');
}

/**
 */
public function testRegisterNamespace():void
{
	$email = 'good@email.com';
	$validator = StandardValidator::getInstance();
	$this->assertTrue($validator->isValidEmail($email));
}

/*=======================================================*/
}
