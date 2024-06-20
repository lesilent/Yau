<?php declare(strict_types=1);

namespace TestNamespace;

use PHPUnit\Framework\TestCase;
use Yau\Validator\StandardValidator;
use Yau\Validator\Standard\Email;
use Yau\Validator\Standard\Ip;
use Yau\Savant\Savant;
use Yau;
use Exception;
use Error;

/*
* This test can be run PHPUnit or directly, which enters the code below
*/
if (spl_autoload_functions() === false)
{
	// Load autoloader
	$AUTOLOAD_FILE = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'autoload.php';
	require $AUTOLOAD_FILE;
	require $AUTOLOAD_FILE;

	// Call some methods
	$email = 'good@email.com';
	$validator = StandardValidator::getInstance();
	$is_valid = $validator->isValidEmail($email);
	assert($is_valid);

	$validator = new Email();
	$is_valid = $validator->isValid($email);
	assert($is_valid);

	// Test unregister autoloader
	Yau::unregisterAutoloader();
	try
	{
		// Line below should fail and throw an Error
		$validator = new Ip();

		// Otherwise throw exception
		throw new Exception('Yau::unregisterAutoloader failed');
	}
	catch (Error $e)
	{
		// Code should reach here
	}

	// Return if unable to load TestCase from PHPUnit
	if (!class_exists('PHPUnit\Framework\TestCase'))
	{
		return;
	}
}

/**
 */
class AutoloadTest extends TestCase
{
/*=======================================================*/

/**
 */
public function testRegister():void
{
	$this->assertTrue(Yau::registerAutoloader());
	foreach (['good@email.com'=>true, 'bademail'=>false] as $email => $expected)
	{
		$validator = StandardValidator::getInstance();
		$this->assertSame($expected, $validator->isValidEmail($email));

		$validator = new Email();
		$this->assertSame($expected, $validator->isValid($email));
	}
}

/**
 */
public function testUnregister():void
{
	$this->assertTrue(Yau::unregisterAutoloader());

	$validator = new Ip();
	$this->assertFalse($validator->isValid('ip'));
}

/*=======================================================*/
}
