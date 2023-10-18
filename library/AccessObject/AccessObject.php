<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_AccessObject
*/

namespace Yau\AccessObject;

use Yau\AccessObject\Exception\InvalidArgumentException;

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
* @author   John Yau
* @category Yau
* @package  Yau_AccessObject
*/
class AccessObject implements \ArrayAccess, \Countable, \Iterator, \Serializable
{
/*=======================================================*/

/**
* Associative array of values in registry
*
* @var array
*/
private $registry = array();

/**
* The value that's returned when a key is undefined
*
* Note: this is initially set to TRUE, but subsequently can be set to an
* empty value when used
*
* @var mixed
*/
protected $undefValue = TRUE;

//-------------------------------------

/**
* Constructor
*
* @param mixed $params either an associative array of parameters or an object
*/
public function __construct($params = array())
{
	if (!empty($params))
	{
		$this->assign($params);
	}
}

/**
 * Set the value for a key
 *
 * @param  string $key   the name of the registry key
 * @param  mixed  $value the value of the parameter
 * @return object the current object
 */
public function set($key, $value)
{
	$this->registry[$key] = $value;
	return $this;
}

/**
 * Return a registry value
 *
 * @param  string $key the name of the registry key
 * @return mixed  the value of the registry key
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
 * @param  mixed  $params either an associative of values, or an AccessObject
 *                object
 * @return object the current object
 */
public function assign($params)
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
		$params = array($params, func_get_arg(1));
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
 * @param string $name the name of template variable to unset
 * @return object the current object
 */
public function clear($key = NULL)
{
	if (is_null($key))
	{
		$this->registry = array();
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
* @param  string $param the name of the parameter
* @return mixed  the value for the parameter
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
public function __set($param, $value)
{
	$this->registry[$param] = $value;
}

/**
* Return whether a parameter is set or not
*
* @param  string  $param the name of the parameter
* @return boolean TRUE if parameter is set, or FALSE if not
*/
public function __isset($param)
{
	return isset($this->registry[$param]);
}

/**
* Unset a parameter
*
* @param string $param the name of the parameter to unset
*/
public function __unset($param)
{
	unset($this->registry[$param]);
}

//-------------------------------------
// Countable interface function

/**
* Return a count of registry values
*
* @return integer the number of registry values
*/
public function count():int
{
	return count($this->registry);
}

//-------------------------------------
// ArrayAccess interface functions

/**
* Returns whether an registry key exists
*
* @param  string  $offet name of registry offset
* @return boolean TRUE if offset exists, or FALSE otherwise
* @link   http://www.php.net/manual/en/class.arrayaccess.php
*/
public function offsetExists($offset):bool
{
	return isset($this->registry[$offset]);
}

/**
* Get an registry value
*
* @param  string $offset name of the registry array offset
* @return mixed  the value of the registry value
* @link   http://www.php.net/manual/en/class.arrayaccess.php
*/
public function offsetGet($offset):mixed
{
	return (!empty($this->undefValue) || array_key_exists($offset, $this->registry))
		? $this->registry[$offset]
		: $this->undefValue;
}

/**
* Set an registry value
*
* @param string $offest name of offset
* @param mixed  $value  the value associated with offset
* @link  http://www.php.net/manual/en/class.arrayaccess.php
*/
public function offsetSet($offset, $value):void
{
	$this->registry[$offset] = $value;
}

/**
* Unset an registry offset
*
* @param string $offset the name of offset
* @link  http://www.php.net/manual/en/class.arrayaccess.php
*/
public function offsetUnset($offset):void
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
* @see    current()
* @link   http://www.php.net/manual/en/class.iterator.php
*/
public function current()
{
	return current($this->registry);
}

/**
* Return the key of the current element in the registry
*
* @return string the key for the current registry value
* @see    key()
* @link   http://www.php.net/manual/en/class.iterator.php
*/
public function key()
{
	return key($this->registry);
}

/**
* Move forward to next element in the registry array
*
* @return mixed the registry value in the next position that's pointed by the
*               internal pointer, or FALSE if there are no more elements
* @see    next()
* @link   http://www.php.net/manual/en/class.iterator.php
*/
public function next()
{
	return next($this->registry);
}

/**
* Rewind the iterator to the first element in the registry
*
* @return mixed the value of the first registry element, or FALSE if the
*               registry is empty
* @see    reset()
* @link   http://www.php.net/manual/en/class.iterator.php
*/
public function rewind()
{
	return reset($this->registry);
}

/**
* Check if there is a current registry element after rewind() or next()
*
* @return boolean TRUE if there is a current element, or FALSE if there isn't
* @link   http://www.php.net/manual/en/class.iterator.php
*/
public function valid()
{
	return (current($this->registry) !== FALSE);
}

//-------------------------------------
// Serializable methods

/**
* Serialize internal registry array and return as a string
*
* @return string
*/
public function serialize()
{
	return serialize($this->registry);
}

/**
* Restore registry from serialized string
*
* @param string $serialized
*/
public function unserialize($serialized)
{
	$this->registry = unserialize($serialized);
}

//-------------------------------------
// ArrayObject methods

/**
* Sort the registry of values by value
*/
public function asort()
{
	asort($this->registry);
}

/**
* Exchange registry array of values for another one
*
* @param  array $object
* @return the old array
*/
public function exchangeArray($params)
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
public function getArrayCopy()
{
	return $this->registry;
}

/**
* Sort the registry of values by key
*/
public function ksort()
{
	ksort($this->registry);
}

/**
* Sort the registry of values using a case insensitive "natural order" algorithm
*/
public function natcasesort()
{
	return natcasesort($this->registry);
}

/**
* Sort the registry of values using a "natural order" algorithm
*/
public function natsort()
{
	return natsort($this->registry);
}

//-------------------------------------

/**
* Return the current registry of values as an associative array
*
* @return array
*/
public function toArray()
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
* @param  string $value the value to be returned when key is undefined
* @throws Exception if value is not considered empty
*/
public function setUndefinedValue($value)
{
	if (!empty($value))
	{
		throw new InvalidArgumentException('Only empty values can be used as undefined');
	}
	$this->undefValue = $value;
}

/*=======================================================*/
}

