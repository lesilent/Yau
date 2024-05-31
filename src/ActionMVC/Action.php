<?php declare(strict_types = 1);

namespace Yau\ActionMVC;

use Yau\ActionMVC\ObjectTrait;

/**
* Abstract action object
*
* @author John Yau
*/
abstract class Action
{
use ObjectTrait;
/*=======================================================*/

/**
 * The main execute function that all actions must implement
 */
abstract public function execute();

/*=======================================================*/
}
