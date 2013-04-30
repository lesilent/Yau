<?php

/**
* Yau Tools Tests
*
* @author  John Yau
*/
namespace YauTest\Validator;

use Yau\Validator\StandardValidator;

/**
*
*/
class StandardValidatorTest extends \PHPUnit_Framework_TestCase
{
/*=======================================================*/

/**
* Standard validator object
*
* @var object
*/
private $validator;

/**
*/
public function setUp()
{
	$this->validator = new StandardValidator();
}

/**
*
*/
public function testAvailableValidators()
{
	$validators = StandardValidator::getAvailableValidators();
	$this->assertTrue(is_array($validators));
	$this->assertTrue(!empty($validators));

	foreach ($validators as $vname)
	{
		$method = 'isValid' . ucfirst($vname);
		$this->assertFalse($this->validator->$method(FALSE));
		$this->assertFalse($this->validator->$method(array()));
	}
}

/**
* @param string $user_agent
* @param array  $result
* @link  http://www.paypalobjects.com/en_US/vhelp/paypalmanager_help/credit_card_numbers.htm
*/
public function testCcnumValidator()
{
	$this->assertFalse($this->validator->isValidIp('12345'));

	foreach (array(
		'378282246310005',
		'371449635398431',
		'378734493671000',
		'6011111111111117',
		'6011000990139424',
		'3530111333300000',
		'3566002020360505',
		'5555555555554444',
		'5105105105105100',
		'4111111111111111',
		'4012888888881881',
		) as $ccnum)
	{
		$this->assertTrue($this->validator->isValidCcnum($ccnum));
	}
}

/**
*/
public function testDateValidator()
{
	$this->assertFalse($this->validator->isValidDate('12345'));
	$this->assertFalse($this->validator->isValidDate('1990-13-13'));
	$this->assertTrue($this->validator->isValidDate('1990-12-13'));
}

/**
*/
public function testDatetimeValidator()
{
	$this->assertFalse($this->validator->isValidDatetime('12345'));
	$this->assertFalse($this->validator->isValidDatetime('1990-13-13 00:00:00'));
	$this->assertTrue($this->validator->isValidDatetime('1990-12-13 12:12:12'));
}

/**
*/
public function testEinValidator()
{
	$this->assertFalse($this->validator->isValidEin('12345'));
	$this->assertTrue($this->validator->isValidEin('134994650'));
	$this->assertTrue($this->validator->isValidEin('13-4994650'));
}

/**
*/
public function testEmailValidator()
{
	$this->assertFalse($this->validator->isValidEmail('12345'));
	$this->assertFalse($this->validator->isValidEmail('test@test.test'));
	$tlds = \Yau\Validator\Standard\Email::getTlds();
	$this->assertTrue(is_array($tlds));
	$this->assertTrue(!empty($tlds));
	foreach ($tlds as $tld)
	{
		$email = 'test@test.' . $tld;
		$this->assertTrue($this->validator->isValidEmail($email));
	}
}

/**
*/
public function testIbanValidator()
{
	$this->assertFalse($this->validator->isValidIban('12345'));
	foreach (array(
		'GR16 0110 1250 0000 0001 2300 695',
		'GB29 NWBK 6016 1331 9268 19',
		'SA03 8000 0000 6080 1016 7519',
		'CH93 0076 2011 6238 5295 7',
		'IL62 0108 0000 0009 9999 999',
		) as $iban)
	{
		$this->assertTrue($this->validator->isValidIban(str_replace(' ', '', $iban)));
	}
}

/**
*/
public function testIpValidator()
{
	$this->assertFalse($this->validator->isValidIp('12345'));
	$this->assertFalse($this->validator->isValidIp('1.1.1.256'));
	$this->assertTrue($this->validator->isValidIp('1.1.1.1'));
}

/**
*/
public function testItinValidator()
{
	$this->assertFalse($this->validator->isValidItin('12345'));
	$this->assertFalse($this->validator->isValidItin('800-70-0000'));
	$this->assertTrue($this->validator->isValidItin('900-70-0000'));
}

/**
*/
public function testSsnValidator()
{
	$this->assertFalse($this->validator->isValidSsn('12345'));
	$this->assertFalse($this->validator->isValidSsn('000-00-0000'));
	$this->assertTrue($this->validator->isValidSsn('123456789'));
	$this->assertTrue($this->validator->isValidSsn('123-45-6789'));
}

/**
*/
public function testTimeValidator()
{
	$this->assertFalse($this->validator->isValidTime('12345'));
	$this->assertFalse($this->validator->isValidTime('24:00:00'));
	$this->assertFalse($this->validator->isValidTime('23:60:00'));
	$this->assertTrue($this->validator->isValidTime('23:59:59'));
}

/**
*/
public function testUrlValidator()
{
	$this->assertFalse($this->validator->isValidUrl('12345'));
	$this->assertFalse($this->validator->isValidUrl('http://'));
	$this->assertTrue($this->validator->isValidUrl('http://test.com'));
}

/*=======================================================*/
}
