<?php

namespace CustomNamespace;

use Yau\ActionMVC\Action;

/**
 * Action under a namespace
 *
 * @author John Yau
 */
class NamespaceAction extends Action
{
/*=======================================================*/

/**
 * The main execute function that all actions must implement
 */
public function execute()
{
	$view = $this->get('view');

	$view->title = __NAMESPACE__;
}

/*=======================================================*/
}
