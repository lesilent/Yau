<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_ActionMVC
*/

namespace Yau\ActionMVC;

use Yau\ActionMVC\AbstractObject;

/**
* Abstract parent
*
* @author   John Yau
* @category Yau
* @package  Yau_ActionMVC
*/
abstract class Action extends AbstractObject
{
/*=======================================================*/

/**
* The main execute function that all actions must implement
*/
abstract public function execute();

/*=======================================================*/
}
