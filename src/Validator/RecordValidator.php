<?php declare(strict_types = 1);

namespace Yau\Validator;

use Yau\Validator\ValidatorException;
use Yau\Validator\ValidatorInterface;

/**
 * Abstract class for validating records
 *
 * Example
 * <code>
 * use Yau\Validate\RecordValidator;
 *
 * // Create a validator class
 * class PersonValidator extends RecordValidator
 * {
 *     public function isValidFirstname($firstname)
 *     {
 *         if (strlen($firstname) > 32)
 *         {
 *             return $this->invalid('Firstname is too long');
 *         }
 *         return true;
 *     }
 *
 *     public function isValidAge($age)
 *     {
 *         if ($age < 18)
 *         {
 *             return $this->invalid('Too young to vote');
 *         }
 *         return true;
 *     }
 *
 * }
 *
 * // Create a person record
 * $person = [
 *     'firstname' => 'John',
 *     'lastname'  => 'Doe',
 *     'age'       => 16,
 * ];
 *
 * // Validate record
 * $validator = new PersonValidator();
 * if (!$validator->isValid($person))
 * {
 *     echo 'The person is invalid for the following reasons:';
 *     foreach ($validator->getMessages() as $field => $message)
 *     {
 *         echo "\t$field: $message\n";
 *     }
 *     exit;
 * }
 * </code>
 *
 * @author John Yau
 */
abstract class RecordValidator implements ValidatorInterface
{
/*=======================================================*/

/**
 * Current field being validated
 *
 * @var string
 */
private $field = '';

/**
 * Associative array to store validation error messages
 *
 * @var array
 */
protected $messages = [];

//-------------------------------------

/**
 * Return the method used to validate a field
 *
 * @param string $field the name of the field
 * @return string the method used to validate field
 * @uses RecordValidator::fieldMethodCase()
 */
protected function getFieldMethod(string $field): string
{
	return 'isValid' . str_replace(' ', '', ucwords(preg_replace('/[^a-z\d]+/i', ' ', $field)));
}

/**
 * Return the associative array of validation errors
 *
 * Example
 * <code>
 * // Instantiate validator
 * $validator = new PersonValidator();
 *
 * // Validate record
 * if (!$validator->isValid($person))
 * {
 *     $person->save();
 * }
 * else
 * {
 *     echo "Person has the following errors\n";
 *     foreach ($person->getMessages() as $field => $message)
 *     {
 *         echo $field, ': ', $message, "\n";
 *     }
 * }
 * </code>
 *
 * @return array an associative array with field names to their corresponding
 *               validation error message
 */
public function getMessages(): array
{
	return $this->messages;
}

/**
 * Add an error message for the current field being validated
 *
 * @param string $message the error message for the field
 * @param string $field optional field for the error message if other than
 *                      the current one
 * @return bool
 */
protected function invalid(string $message, ?string $field = null): bool
{
	if (!isset($field))
	{
		$field = $this->field;
	}
	$this->messages[$field] = $message;
	return false;
}

/**
 * Validate the value in the field of the current record
 *
 * @param mixed $value an iterable array or object to validate
 * @return bool true if value is valid, or false if not
 */
public function isValid($value): bool
{
	// Clear out error messages
	$this->messages = [];

	// Validate record
	$result = true;
	foreach ($value as $field => $val)
	{
		if (($method = $this->getFieldMethod($field)) && method_exists($this, $method))
		{
			try
			{
				$this->field = $field;
				if ($this->$method($val))
				{
					continue;
				}
				throw new ValidatorException('Invalid ' . $field);
			}
			catch (ValidatorException $e)
			{
				$result = $this->invalid($e->getMessage(), $field) && $result;
			}
		}
	}

	// Return result
	return $result;
}

/*=======================================================*/
}
