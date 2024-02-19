<?php

use Yau\ActionMVC\Action;

/**
 * Action without a namespace
 *
 * @author John Yau
 */
class NospaceAction extends Action
{
/*=======================================================*/

/**
 * The main execute function that all actions must implement
 */
public function execute()
{
	$view = $this->get('view');

	$view->title = 'Nospace';
}

/*=======================================================*/
}
