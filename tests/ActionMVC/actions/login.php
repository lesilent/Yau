<?php

use Yau\ActionMVC\Action;

/**
 * Login action
 *
 * @author John Yau
 */
class LoginAction extends Action
{
/*=======================================================*/

/**
 * The main execute function that all actions must implement
 */
public function execute()
{
	$view = $this->get('view');

	$view->title = 'login';
}

/*=======================================================*/
}
