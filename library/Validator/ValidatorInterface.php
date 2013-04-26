<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_Validator
*/

namespace Yau\Validator;

/**
*
* @author   John Yau
* @category Yau
* @package  Yau_Validator
*/
interface ValidatorInterface
{
/*=======================================================*/

/**
* The main validation method
*
* @param  mixed   $value
* @return boolean
*/
public function isValid($value);

/*=======================================================*/
}
