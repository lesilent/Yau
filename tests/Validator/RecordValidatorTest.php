<?php

/**
* Yau Tools Tests
*
* @author  John Yau
*/
namespace YauTest\Validator;

use Yau\Validator\RecordValidator;

/**
*
*/
class TestRecordValidator extends RecordValidator
{
/*=======================================================*/

/**
*
*/
public function isValidFirstName($value)
{
	if (!empty($value) && strlen($value) > 32)
	{
		return $this->falseMessage('First name is too long');
	}
	return TRUE;
}

/**
*/
public function isValidAge($value)
{
	if ($value < 18)
	{
		return $this->falseMessage('Too young to vote');
	}
	return TRUE;
}

/*=======================================================*/
}

/**
*
*/
class RecordValidatorTest extends \PHPUnit_Framework_TestCase
{
/*=======================================================*/

/**
* Test record validator object
*
* @var object
*/
private $validator;

/**
*/
public function setUp()
{
	$this->validator = new TestRecordValidator();
}

/**
*/
public function testValidator()
{
	$record = array('firstname'=>str_repeat('X', 33), 'lastname'=>'Doe', 'age'=>12);
	$is_valid = $this->validator->isValid($record);
	$messages = $this->validator->getMessages();
	$this->assertFalse($is_valid);
	$this->assertTrue(is_array($messages));
	$this->assertCount(2, $messages);
	$this->assertArrayHasKey('firstname', $messages);
	$this->assertArrayHasKey('age', $messages);

	$record['age'] = 18;
	$is_valid = $this->validator->isValid($record);
	$messages = $this->validator->getMessages();
	$this->assertFalse($is_valid);
	$this->assertCount(1, $messages);
	$this->assertArrayHasKey('firstname', $messages);

	$record['firstname'] = 'John';
	$is_valid = $this->validator->isValid($record);
	$messages = $this->validator->getMessages();
	$this->assertTrue($is_valid);
	$this->assertEmpty($messages);
}

/*=======================================================*/
}
