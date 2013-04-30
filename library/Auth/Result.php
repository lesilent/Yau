<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_Auth
*/

namespace Yau\Auth;

/**
* Authentication result class
*
* @author   John Yau
* @category Yau
* @package  Yau_Auth
*/
class Result
{
/*=======================================================*/

/**
* The result code
*
* @var integer
*/
protected $code = 0;

/**
* The result message
*
* @var string
*/
protected $message;

/**
* Constructor
*
* @param mixed  $code    either a numeric or boolean result code
* @param string $message the result message
*/
public function __construct($code, $message = NULL)
{
	if (is_bool($code))
	{
		$this->code = ($code) ? 0 : 1;
	}
	$this->message = $message;
}

/**
* @return integer
*/
public function getCode()
{
	return $this->code;
}

/**
* @return string
*/
public function getMessage()
{
	return $this->message;
}

/**
* Return whether the result represents a successful authentication
*
* @return boolean
*/
public function isValid()
{
	return ($this->code == 0);
}

/*=======================================================*/
}
