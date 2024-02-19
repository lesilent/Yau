<?php

use Yau\ActionMVC\Action;

/**
 * Action to show id
 *
 * @author John Yau
 */
class ShowAction extends Action
{
/*=======================================================*/

/**
 * The main execute function that all actions must implement
 */
public function execute()
{
	$controller = $this->get('controller');
	$view       = $this->get('view');

	$view->title = $controller->getId();
}

/*=======================================================*/
}
