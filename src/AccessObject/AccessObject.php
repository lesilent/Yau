<?php declare(strict_types = 1);

namespace Yau\AccessObject;

use InvalidArgumentException;

/**
 * A class for providing ArrayAccess and magic getters and setters
 *
 * Example
 * <code>
 * use Yau\AccessObject\AccessObject;
 *
 * // Create a new object
 * $obj = new AccessObject();
 *
 * // Assign values
 * $obj->fname = 'John';
 * $obj['lname'] = 'Doe';
 *
 * // Assign multiple values
 * $values = array(
 *     'age'   => 18,
 *     'state' => 'AZ',
 * );
 * $obj->assign($values);
 *
 * // Retrieving values
 * echo $obj->fname, ' ' , $obj['lname'], ' lives in ', $obj->state;
 * </code>
 *
 * @author John Yau
 */
class AccessObject implements \ArrayAccess, \Countable, \Iterator, \Serializable
{
/*=======================================================*/

/**
 * Associative array of values in registry
 *
 * @var array
 */
private $registry = [];

/**
 * The value that's returned when a key is undefined
 *
 * Note: this is initially set to true, but subsequently can be set to an
 * empty value when used
 *
 * @var mixed
 */
protected $undefValue = true;

//-------------------------------------

/**
 * Constructor
 *
 * @param mixed $params either an associative array of parameters or an object
 */
public function __construct(mixed $params = null)
{
	if (!empty($params))
	{
		$this->assign($params);
	}
}

/**
 * Set the value for a key
 *
 * @param string $key the name of the registry key
 * @param mixed $value the value of the parameter
 * @return object the current object
 */
public function set($key, mixed $value): object
{
	$this->registry[$key] = $value;
	return $this;
}

/**
 * Return a registry value
 *
 * @param string $key the name of the registry key
 * @return mixed the value of the registry key
 */
public function get($key)
{
	return (!empty($this->undefValue) || array_key_exists($key, $this->registry))
		? $this->registry[$key]
		: $this->undefValue;
}

/**
 * Assign one or more values to registry
 *
 * @param mixed $params either an associative of values, or an AccessObject object
 * @return object the current object
 */
public function assign($params): object
{
	// Convert objects to an array
	if (is_object($params))
	{
		$params = ($params instanceof AccessObject)
			? $params->toArray()
			: get_object_vars($params);
	}
	// Handle cases where function is passed two parameters like get()
	elseif (func_num_args() == 2 && is_scalar($params))
	{
		$params = [$params, func_get_arg(1)];
	}

	// Assign parameters
	if (is_array($params))
	{
		$this->registry = array_merge($this->registry, $params);
	}
	else
	{
		throw new InvalidArgumentException('Cannot assign values of type ' . gettype($params));
	}

	// Return this object
	return $this;
}

/**
 * Unset or clear one or all values in registry
 *
 * @param string $key the name of template variable to unset
 * @return object the current object
 */
public function clear($key = null): object
{
	if (is_null($key))
	{
		$this->registry = [];
	}
	elseif (is_array($key))
	{
		foreach ($key as $param)
		{
			unset($this->registry[$param]);
		}
	}
	elseif (is_scalar($key))
	{
		unset($this->registry[$key]);
	}
	return $this;
}

//-------------------------------------

/**
 * Get the value for a parameter
 *
 * @param string $param the name of the parameter
 * @return mixed the value for the parameter
 */
public function &__get($param)
{
	if (!empty($this->undefValue) || array_key_exists($param, $this->registry))
	{
		return $this->registry[$param];
	}
	else
	{
		return $this->undefValue;
	}
}

/**
 * Set a value for a parameter
 *
 * @param string $param the name of the parameter
 * @param mixed  $value the value for the parameter
 */
public function __set($param, $value): void
{
	$this->registry[$param] = $value;
}

/**
 * Return whether a parameter is set or not
 *
 * @param string $param the name of the parameter
 * @return bool true if parameter is set, or false if not
 */
public function __isset($param): bool
{
	return isset($this->registry[$param]);
}

/**
 * Unset a parameter
 *
 * @param string $param the name of the parameter to unset
 */
public function __unset($param): void
{
	unset($this->registry[$param]);
}

//-------------------------------------
// Countable interface function

/**
 * Return a count of registry values
 *
 * @return int the number of registry values
 */
public function count(): int
{
	return count($this->registry);
}

//-------------------------------------
// ArrayAccess interface functions

/**
 * Returns whether an registry key exists
 *
 * @param string $offset name of registry offset
 * @return bool true if offset exists, or false otherwise
 * @link http://www.php.net/manual/en/class.arrayaccess.php
 */
public function offsetExists($offset): bool
{
	return isset($this->registry[$offset]);
}

/**
 * Get an registry value
 *
 * @param string $offset name of the registry array offset
 * @return mixed the value of the registry value
 * @link http://www.php.net/manual/en/class.arrayaccess.php
 */
#[\ReturnTypeWillChange]
public function offsetGet($offset)
{
	return (!empty($this->undefValue) || array_key_exists($offset, $this->registry))
		? $this->registry[$offset]
		: $this->undefValue;
}

/**
 * Set an registry value
 *
 * @param string $offset name of offset
 * @param mixed $value the value associated with offset
 * @link http://www.php.net/manual/en/class.arrayaccess.php
 */
public function offsetSet($offset, $value): void
{
	$this->registry[$offset] = $value;
}

/**
 * Unset an registry offset
 *
 * @param string $offset the name of offset
 * @link http://www.php.net/manual/en/class.arrayaccess.php
 */
public function offsetUnset($offset): void
{
	unset($this->registry[$offset]);
}

//-------------------------------------
// Iterator interface functions

/**
 * Return the current element in the registry array
 *
 * @return mixed the current registry value being pointed to by the internal
 *               pointer
 * @see current()
 * @link http://www.php.net/manual/en/class.iterator.php
 */
#[\ReturnTypeWillChange]
public function current()
{
	return current($this->registry);
}

/**
 * Return the key of the current element in the registry
 *
 * @return string the key for the current registry value
 * @see key()
 * @link http://www.php.net/manual/en/class.iterator.php
 */
#[\ReturnTypeWillChange]
public function key()
{
	return key($this->registry);
}

/**
 * Move forward to next element in the registry array
 *
 * @see next()
 * @link http://www.php.net/manual/en/class.iterator.php
 */
public function next(): void
{
	next($this->registry);
}

/**
 * Rewind the iterator to the first element in the registry
 *
 * @see reset()
 * @link http://www.php.net/manual/en/class.iterator.php
 */
public function rewind(): void
{
	reset($this->registry);
}

/**
 * Check if there is a current registry element after rewind() or next()
 *
 * @return bool true if there is a current element, or false if there isn't
 * @link http://www.php.net/manual/en/class.iterator.php
 */
public function valid(): bool
{
	return (current($this->registry) !== false);
}

//-------------------------------------
// Serializable methods

/**
 * Return associative array of internal registry
 *
 * @return array
 */
public function __serialize(): array
{
	return $this->registry;
}

/**
 * Take associative array of values and store into internal registry
 */
public function __unserialize(array $data): void
{
	$this->registry = $data;
}

/**
 * Serialize internal registry array and return as a string
 *
 * @return string
 */
public function serialize(): ?string
{
	return serialize($this->registry);
}

/**
 * Restore registry from serialized string
 *
 * @param string $serialized
 */
public function unserialize($serialized): void
{
	$this->registry = unserialize($serialized);
}

//-------------------------------------
// ArrayObject methods

/**
 * Sort the registry of values by value
 *
 * @return bool
 */
public function asort(): bool
{
	return asort($this->registry);
}

/**
 * Exchange registry array of values for another one
 *
 * @param mixed $params
 * @return array the old array
 */
public function exchangeArray(mixed $params): array
{
	$registry = $this->registry;
	$this->clear();
	$this->assign($params);
	return $registry;
}

/**
 * Return the current registry of values as an associative array
 *
 * @return array
 */
public function getArrayCopy(): array
{
	return $this->registry;
}

/**
 * Sort the registry of values by key
 *
 * @return bool
 */
public function ksort(): bool
{
	return ksort($this->registry);
}

/**
 * Sort the registry of values using a case insensitive "natural order" algorithm
 *
 * @return bool
 */
public function natcasesort(): bool
{
	return natcasesort($this->registry);
}

/**
 * Sort the registry of values using a "natural order" algorithm
 *
 * @return bool
 */
public function natsort(): bool
{
	return natsort($this->registry);
}

//-------------------------------------

/**
 * Return the current registry of values as an associative array
 *
 * @return array
 */
public function toArray(): array
{
	return $this->registry;
}

/**
 * Sets the empty return value when retrieving an undefined key
 *
 * Example
 * <code>
 * $obj = new AccessObject();
 *
 * // Triggers PHP noticed of "Undefined index:  test"
 * echo $obj['test'];
 *
 * // Set undefined value
 * $obj->setUndefinedValue('');
 *
 * // Outputs empty string
 * echo $obj['test'];
 * </code>
 *
 * @param string $value the value to be returned when key is undefined
 * @throws InvalidArgumentException if value is not considered empty
 */
public function setUndefinedValue($value): void
{
	if (!empty($value))
	{
		throw new InvalidArgumentException('Only empty values can be used as undefined');
	}
	$this->undefValue = $value;
}

/*=======================================================*/
}

