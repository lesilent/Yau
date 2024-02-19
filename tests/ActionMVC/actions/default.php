<?php

use Yau\ActionMVC\Action;

/**
 * Default action
 *
 * @author John Yau
 */
class DefaultAction extends Action
{
/*=======================================================*/

/**
 * The main execute function that all actions must implement
 */
public function execute()
{
	$view = $this->get('view');

	$view->title = 'default';
}

/*=======================================================*/
}
