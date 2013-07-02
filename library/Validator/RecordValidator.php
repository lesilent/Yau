<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_Validate
*/

namespace Yau\Validator;

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
*             $this->addMessage('Firstname is too long');
*             return FALSE;
*         }
*         return TRUE;
*     }
*
*     public function isValidAge($age)
*     {
*         if ($age < 18)
*         {
*             return $this->falseMessage('Too young to vote');
*         }
*         return TRUE;
*     }
* }
*
* // Create a person record
* $person = array(
*     'firstname' => 'John',
*     'lastname'  => 'Doe',
*     'age'       => 16,
* );
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
* @author   John Yau
* @category Yau
* @package  Yau_Validate
*/
abstract class RecordValidator implements ValidatorInterface
{
/*=======================================================*/

/**
* The current record being validated
*
* Note: this is protected instead of private in case the validator needs to
* modify the values during the validation process
*
* @var mixed
*/
protected $record;

/**
* The current field being validated;
*
* @var string
*/
private $field;

/**
* Associative array to store validation error messages
*
* @var array
*/
protected $messages = array();

//-------------------------------------

/**
* Get the appropriate case of a method based on a field
*
* Example
* <code>
* echo $this->fieldMethodCase('firstname');   // outputs Firstname
* echo $this->fieldMethodCase('firstName');   // outputs FirstName
* echo $this->fieldMethodCase('first_name');  // outputs FirstName
* </code>
*
* @param  string $field the name of the field
* @return string the suffix of the method name used to validate field
*/
protected function fieldMethodCase($field)
{
	return str_replace(' ', '', ucwords(preg_replace('/[^a-z\d]+/i', ' ', $field)));
}

/**
* Return the method used to validate a field
*
* @param  string $field the name of the field
* @return string the method used to validate field
* @uses   RecordValidator::fieldMethodCase()
*/
protected function getFieldMethod($field)
{
	return 'isValid' . $this->fieldMethodCase($field);
}

/**
* Return the current record being validated
*
* @return mixed the current record
*/
protected function getRecord()
{
	return (isset($this->record)) ? $this->record : NULL;
}

/**
* Return the value for a field from the current record
*
* @param  string $field
* @return mixed
*/
protected function getRecordValue($field)
{
	return (isset($this->record[$field])) ? $this->record[$field] : NULL;
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
public function getMessages()
{
	return $this->messages;
}

/**
* Set the field that's currently being validated
*
* @param string $field
*/
protected function setField($field)
{
	$this->field = $field;
}

/**
* Add an error message for the current field being validated
*
* @param string $message the error message for the field
* @param string $field   optional field for the error message if other than
*                        the current one
*/
protected function addMessage($message, $field = NULL)
{
	if (isset($field))
	{
		$this->messages[$field] = $message;
	}
	elseif (!empty($this->field))
	{
		$this->messages[$this->field] = $message;
	}
	else
	{
		$this->messages[] = $message;
	}
}

/**
* Add an error message and return FALSE
*
* Example
* <code>
* $message = 'Username is too long';
*
* // The following are equivalent
*
* $this->addMessage('Username is too long');
* return FALSE;
*
* return $this->falseMessage('Username is too long');
* </code>
*
* @param  string  $message the error message for the field
* @return boolean FALSE is always returned
*/
protected function falseMessage($message)
{
	$this->addMessage($message);
	return FALSE;
}

/**
* Validate the value in the field of the current record
*
* Example
* <code>
* </code>
*
* @param  mixed   $value an iterator-able array or object to validate
* @return boolean TRUE if value is valid, or FALSE if not
* @throws Exception if value is not valid
*/
public function isValid($value)
{
	// Clear out error messages
	$this->messages = array();

	// Store record
	$this->record = $value;

	// Validate record
	$result = TRUE;
	foreach ($this->record as $field => $value)
	{
		$method = $this->getFieldMethod($field);
		if (method_exists($this, $method))
		{
			$this->setField($field);
			try
			{
				$result = (bool) $this->$method($value) && $result;
			}
			catch (\Exception $e)
			{
				$this->addMessage($e->getMessage());
			}
			$this->setField(NULL);
		}
	}
	$this->record = NULL;

	// Return result
	return $result;
}

/*=======================================================*/
}
