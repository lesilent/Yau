<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Yau\Validator\StandardValidator;
use Yau\Validator\Standard\Ccnum;
use Yau\Validator\Standard\Date;
use Yau\Validator\Standard\Datetime;
use Yau\Validator\Standard\Ein;
use Yau\Validator\Standard\Email;
use Yau\Validator\Standard\Iban;
use Yau\Validator\Standard\Ip;
use Yau\Validator\Standard\Itin;
use Yau\Validator\Standard\Ssn;
use Yau\Validator\Standard\Time;
use Yau\Validator\Standard\Url;
use Yau\Validator\Standard\Zip;

/**
 * Tests for Yau\Validator\StandardValidator
 */
class StandardValidatorTest extends TestCase
{
/*=======================================================*/

/**
 */
public function testsGetAvailableValidators():void
{
	$validators = StandardValidator::getAvailableValidators();
	$this->assertIsArray($validators);
	$this->assertContains('email', $validators);
}

/**
 * @return array
 * @link https://stripe.com/docs/testing
 */
public function ccnumProvider():array
{
	return [
		[true, '371449635398431'],
		[true, '378282246310005'],
		[true, '378734493671000'],
		[true, '3530111333300000'],
		[true, '3566002020360505'],
		[true, '4012888888881881'],
		[true, '4111111111111111'],
		[true, '4242424242424242'],
		[true, '5105105105105100'],
		[true, '5555555555554444'],
		[true, '6011111111111117'],
		[true, '6011000990139424'],
		[false, '123456789'],
	];
}

/**
 * @param bool $expected
 * @param string $ccnum
 * @dataProvider ccnumProvider
 */
public function testCcnum($expected, $ccnum):void
{
	$validator = StandardValidator::getInstance();
	$this->assertSame($expected, $validator->isValidCcnum($ccnum));

	$validator = new Ccnum();
	$this->assertSame($expected, $validator->isValid($ccnum));
}

/**
 * @return array
 */
public function dateProvider():array
{
	return [
		[true, '2024-01-01'],
		[true, '2024-12-31'],
		[false, '2024-00-01'],
		[false, '2024-01-00'],
		[false, '2024-01-32'],
		[false, '2024-02-30'],
		[false, '2024-02-31'],
		[false, '2024-12-32'],
		[false, '2024-13-01'],
		[false, '2024-12-00'],
	];
}

/**
 * @param bool $expected
 * @param string $date
 * @dataProvider dateProvider
 */
public function testDate($expected, $date):void
{
	$validator = StandardValidator::getInstance();
	$this->assertSame($expected, $validator->isValidDate($date));

	$validator = new Date();
	$this->assertSame($expected, $validator->isValid($date));
}

/**
 * @param bool $expected
 * @param string $date
 * @dataProvider dateProvider
 */
public function testDatetime($expected, $date):void
{
	$times = ['valid'=>[], 'invalid'=>[]];
	foreach ($this->timeProvider() as $row)
	{
		$times[$row[0] ? 'valid' : 'invalid'][] = $row[1];
	}

	$validator = StandardValidator::getInstance();
	foreach ($this->timeProvider() as $row)
	{
		$this->assertSame($expected && $row[0], $validator->isValidDatetime($date . ' ' . $row[1]));
	}

	$validator = new Datetime();
	foreach ($this->timeProvider() as $row)
	{
		$this->assertSame($expected && $row[0], $validator->isValid($date . ' ' . $row[1]));
	}
}

/**
 * @return array
 */
public function einProvider():array
{
	return [
		[true, '12-3456789'],
		[false, '00-123456'],
		[false, '17-123456'],
	];
}

/**
 * @param bool $expected
 * @param string $ein
 * @dataProvider einProvider
 */
public function testEin($expected, $ein):void
{
	$validator = StandardValidator::getInstance();
	$this->assertSame($expected, $validator->isValidEin($ein));

	$validator = new Ein();
	$this->assertSame($expected, $validator->isValid($ein));
}

/**
 * @return array
 */
public function emailProvider():array
{
	return [
		[true, 'hello@domain.net'],
		[true, 'hello+world@domain.com'],
		[true, 'hello.test@domain.org'],
		[true, 'hello+dot@sub.test.co.uk'],
		[false, 'domain.cn'],
		[false, 'bad.1com'],
	];
}

/**
 * @dataProvider emailProvider
 */
public function testEmail($expected, $email):void
{
	$validator = StandardValidator::getInstance();
	$this->assertSame($expected, $validator->isValidEmail($email));

	$validator = new Email();
	$this->assertSame($expected, $validator->isValid($email));
}

/**
 */
public function testEmailValidator():void
{
	$validator = new Email();

	$tlds = $validator->getTlds();
	$this->assertIsArray($tlds);
	$this->assertContains('com', $tlds);
	$this->assertContains('net', $tlds);
}

/**
 * @return array
 */
public function ibanProvider():array
{
	return [
		[true, 'GR16 0110 1250 0000 0001 2300 695'],
		[true, 'GB29 NWBK 6016 1331 9268 19'],
		[true, 'SA03 8000 0000 6080 1016 7519'],
		[true, 'CH93 0076 2011 6238 5295 7'],
		[true, 'IL62 0108 0000 0009 9999 999'],
	];
}

/**
 * @param bool $expected
 * @param string $iban
 * @dataProvider ibanProvider
 */
public function testIban($expected, $iban):void
{
	$validator = StandardValidator::getInstance();
	$this->assertSame($expected, $validator->isValidIban($iban));

	$validator = new Iban();
	$this->assertSame($expected, $validator->isValid($iban));
}

/**
 * @return array
 */
public function ipProvider():array
{
	return [
		[true, '1.1.1.1'],
		[true, '192.168.0.0'],
		[false, '256.1.1.1'],
		[false, '1.1.1.256'],
	];
}

/**
 * @param bool $expected
 * @param string $ip
 * @dataProvider ipProvider
 */
public function testIp($expected, $ip):void
{
	$validator = StandardValidator::getInstance();
	$this->assertSame($expected, $validator->isValidIp($ip));

	$validator = new Ip();
	$this->assertSame($expected, $validator->isValid($ip));
}

/**
 * @return array
 */
public function itinProvider():array
{
	return [
		[true, '900-71-1234'],
		[true, '900-81-1234'],
		[false, '800-71-1234'],
		[false, '900-61-1234'],
	];
}

/**
 * @param bool $expected
 * @param string $itin
 * @dataProvider itinProvider
 */
public function testItin($expected, $itin):void
{
	$validator = StandardValidator::getInstance();
	$this->assertSame($expected, $validator->isValidItin($itin));

	$validator = new Itin();
	$this->assertSame($expected, $validator->isValid($itin));
}

/**
 * @return array
 */
public function ssnProvider():array
{
	return [
		[true, '078-05-1120'],
		[true, '600-12-1234'],
		[false, '000-26-6781'],
		[false, '324-00-6781'],
		[false, '324-26-0000'],
		[false, '324-26-000'],
		[false, '324-26-00000'],
		[false, '999-99-9999'],
	];
}

/**
 * @param bool $expected
 * @param string $ssn
 * @dataProvider ssnProvider
 */
public function testSsn($expected, $ssn):void
{
	$validator = StandardValidator::getInstance();
	$this->assertSame($expected, $validator->isValidSsn($ssn));

	$validator = new Ssn();
	$this->assertSame($expected, $validator->isValid($ssn));
}

/**
 * @return array
 */
public function timeProvider():array
{
	return [
		[true, '00:00:00'],
		[true, '23:59:59'],
		[false, '24:00:00'],
		[false, '23:60:00'],
		[false, '12345'],
	];
}

/**
 * @param bool $expected
 * @param string $time
 * @dataProvider timeProvider
 */
public function testTime($expected, $time):void
{
	$validator = StandardValidator::getInstance();
	$this->assertSame($expected, $validator->isValidTime($time));

	$validator = new Time();
	$this->assertSame($expected, $validator->isValid($time));
}

/**
 * @return array
 */
public function urlProvider():array
{
	return [
		[true, 'http://example.com'],
		[true, 'https://example.com'],
		[false, 'https://'],
		[false, '12345'],
	];
}

/**
 * @param bool $expected
 * @param string $url
 * @dataProvider urlProvider
 */
public function testUrl($expected, $url):void
{
	$validator = StandardValidator::getInstance();
	$this->assertSame($expected, $validator->isValidUrl($url));

	$validator = new Url();
	$this->assertSame($expected, $validator->isValid($url));
}

/**
 * @return array
 */
public function zipProvider():array
{
	return [
		[true, '85713'],
		[true, '20500'],
		[true, '20500-0004'],
		[false, '1234'],
		[false, '123456'],
		[false, '12345-67890'],
	];
}

/**
 * @param bool $expected
 * @param string $zip
 * @dataProvider zipProvider
 */
public function testZip($expected, $zip):void
{
	$validator = StandardValidator::getInstance();
	$this->assertSame($expected, $validator->isValidZip($zip));

	$validator = new Zip();
	$this->assertSame($expected, $validator->isValid($zip));
}

/*=======================================================*/
}
