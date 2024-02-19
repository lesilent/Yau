<?php

namespace Yau\Validator;

/**
 * Interface for validators
 *
 * @author John Yau
 */
interface ValidatorInterface
{
/*=======================================================*/

/**
 * The main validation method
 *
 * @param mixed $value
 * @return bool
 */
public function isValid($value):bool;

/*=======================================================*/
}
