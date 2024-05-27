<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Yau\Validator\RecordValidator;

/**
 * Tests for Yau\Validator\RecordValidator
 */
class RecordValidatorTest extends TestCase
{
/*=======================================================*/

/**
 * @return array
 */
public function recordProvider():array
{
	return [
		[true, ['Age'=>18, 'Email'=>'test@example.com', 'Name'=>'John']],
		[false, ['Age'=>17, 'Email'=>'test@example.com', 'Name'=>'']],
		[false, ['Age'=>18, 'Email'=>'bademail.com', 'Name'=>'John']],
	];
}

/**
 * @param bool $expected
 * @param array $record
 * @dataProvider recordProvider
 */
public function testValidator($expected, $record):void
{
	$validator = new class extends RecordValidator {
		public function isValidAge($value)
		{
			return ($value >= 18);
		}
		public function isValidEmail($value)
		{
			return (filter_var($value, FILTER_VALIDATE_EMAIL) !== false);
		}
		public function isValidName($value)
		{
			$length = strlen($value);
			return ($length > 0 && $length < 20);
		}
	};

	$this->assertSame($expected, $validator->isValid($record));
}

/*=======================================================*/
}
