<?php declare(strict_types = 1);

namespace Yau\ActionMVC;

use Yau\ActionMVC\Request;
use Yau\ActionMVC\View;
use ReflectionClass;
use InvalidArgumentException;
use RuntimeException;
use Exception;

/**
 * A simple all-on-one controller class
 *
 * The purpose of which is to do away with dealing with complicated routing
 * and multiple action dispatching and use a simple mapping of one "action"
 * per file.
 *
 * This object can be extended to add additional functionality as needed.
 *
 * Example controller
 * <code>
 * use Yau\ActionMVC\Controller;
 *
 * class MyController extends Controller
 * {
 *     private static $DB_DSN = 'mysql:dbname=testdb;host=127.0.0.1';
 *     private static $DB_USER = 'dbuser';
 *     private static $DB_PASS = 'dbpass';
 *
 *     public function __construct()
 *     {
 *         $dbh = new PDO(self::$DB_DSN, self::$DB_USER, self::$DB_PASS);
 *         $this->setVariable('dbh, $dbh);
 *     }
 *
 *     public function getUsers()
 *     {
 *         return ['picard', 'riker', 'worf', 'data', 'troi'];
 *     }
 *
 *     public function ageNextYear($age)
 *     {
 *         return $age + 1;
 *     }
 * }
 *
 * // Instantiate controller
 * $controller = new MyController();
 *
 * // Configure controller
 * $controller->setActionPath('/home/mysite.com/actions');
 *
 * // Run controller
 * $controller->run();
 * </code>
 *
 * Example Directory Structure
 * <code>
 * /actions/
 * /actions/index.php
 * /actions/list_users.php
 * /actions/email_admin.php
 * /actions/delete_file.php
 *
 * /displays/
 * /displays/faq.php
 * /displays/widget/header.php
 * /displays/widget/footer.php
 * /displays/widget/vote_form.php
 * </code>
 *
 * Example action
 * <code>
 * // Get person id from request parameters
 * $person_id = intval($request['person_id']);
 *
 * // Query database
 * $sql = 'SELECT fname, lname, age FROM person WHERE person_id = ' . $person_id;
 * $sth = $dbh->query($sql);
 * $row = $sth->fetch();
 *
 * // Pass what you need to view
 * $view = $controller->getView();
 * $view->fname = $row['fname'];
 * $view->lname = $row['lname'];
 * $view->next_age = $controller->ageNextYear($row['age']);
 * $view->members = $controller->getUsers();
 *
 * // Display view
 * $body = $view->render('profile.inc.html');
 *
 * $response->appendBody($body);
 * </code>
 *
 * Example display of profile.inc.html
 * <code>
 * <?php $this->display('header.inc.html'); ?>
 *
 * Welcome <?php echo $fname; ?>
 *
 * Your last name is: <?php echo $lname; ?>
 * You age next year will be: <?php echo $next_age; ?>
 *
 * Current members:
 * <ul>
 * <? foreach ($members as $user): ?>
 * <li><?php echo $user; ?>
 * <?php endforeach; ?>
 * </ul>
 *
 * <?php $this->display('footer.inc.html'); ?>
 * </code>
 *
 * // Set request and response objects if using something other than default
 * $controller->setRequest($request);
 * $controller->setResponse($response);
 *
 * // Set additional parameters
 * $myhelper = new MyHelper();
 * $controller->setVariable('myhelper', $myhelper);
 *
 * $request->set($request->getActionName(), 'list');
 * $controller->run();
 *
 * $response['enc']= $request['name'];
 * </code>
 *
 * Example of display file
 * <code>
 * $str = 'My name is ' $fname;
 * $response->setBody($str);
 * The answer is response['s'];
 *
 * $response->appendBody($view->render('header.inc'));
 * $response->appendBody($view->render('footer.inc');
 * $response->appendBody($view->render('test');
 * </code>
 *
 * When an action file is loaded, the following variables are in the available
 * in its name space:
 * <ol>
 * <li>controller
 * <li>request
 * <li>response
 * <li>context
 * <li>view
 * </ol>
 *
 * Note: actions != commands   (an action can be comprised of one more or more commands)
 *
 * Example of an action class
 * <code>
 * class EditAction extends Action
 * {
 *     public function execute()
 *     {
 *          $action = $this->get('action', 'asdf');
 *     }
 * }
 * </code>
 *
 * @author John Yau
 */
class Controller
{
/*=======================================================*/

/**
 * Objects used by controller
 *
 * @var array
 */
private $objects = [];

/**
 * Base path to the MVC files
 *
 * @var string
 */
private $path = '.';

/**
 * The action name
 *
 * @var string the default is "action"
 */
private $actionName = 'action';

/**
 * The default action
 *
 * @var string the default is "default"
 */
private $defaultAction = 'default';

/**
 * Return the path to the MVC files
 *
 * @return string
 */
public function getBasePath()
{
	return $this->path;
}

/**
 * Sets the path to the MVC files
 *
 * @param string $path
 */
public function setBasePath($path)
{
	$this->path = realpath($path);
}

/**
 * Return the class name for a type and name
 *
 * @param string $type
 * @param string $name
 * @return string
 */
public function getClassName($type, $name = 'default')
{
	$called_class = get_called_class();
	return ((($pos = strrpos($called_class, '\\')) === false)
		? '' : substr($called_class, 0, $pos + 1))
		. str_replace(' ', '', ucwords(str_replace('_', ' ', $name . '_' . $type)));
}

/**
 * Return the filename for a type and name
 *
 * @param string $type
 * @param string $name
 * @return string
 */
public function getFileName($type, $name = 'default')
{
	return $this->path . DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $type) . 's' . DIRECTORY_SEPARATOR . $name . '.php';
}

/**
 * Return a MVC object
 *
 * @param string $type the type of object to return
 * @param string $name name of object to return
 * @return object
 */
public function get($type, $name = 'default')
{
	// Check arguments
	if (!preg_match('/^\w+$/', $type))
	{
		throw new InvalidArgumentException('Invalid MVC object type ' . $type);
	}
	if (!preg_match('/^\w+$/', $name))
	{
		throw new InvalidArgumentException('Invalid MVC object name ' . $name);
	}

	// Check whether object already exists
	$class_name = $this->getClassName($type, $name);
	if (empty($this->objects[$class_name]))
	{
		// Special case for returning default objects
		if ($name == 'default')
		{
			if ($type == 'controller')
			{
				return $this;
			}
			elseif ($type == 'request')
			{
				$request = Request::getInstance();
				$request->setController($this);
				return $this->objects[$class_name] = $request;
			}
			elseif ($type == 'view')
			{
				$view = View::getInstance();
				$view->setController($this);
				return $this->objects[$class_name] = $view;
			}
		}

		// Load file if there's no class for it
		if (!class_exists($class_name))
		{
			$filename = $this->getFileName($type, $name);
			if (!include($filename))
			{
				throw new RuntimeException("Unable to load $name $type");
			}
		}

		// Instantiate object
		$this->objects[$class_name] = true;
		if (class_exists($class_name))
		{
			$reflect = new ReflectionClass($class_name);
			if (!$reflect->hasMethod('__construct')
				|| $reflect->getMethod('__construct')->isPublic())
			{
				$instance = $reflect->newInstance();
				if ($reflect->hasMethod('setController'))
				{
					$instance->setController($this);
				}
				$this->objects[$class_name] = $instance;
			}
		}
	}
	return $this->objects[$class_name];
}

/**
 * Set a MVC object or value
 *
 * Examples
 * <code>
 * // Set the default object
 * $controller->set('request', $request);
 *
 * // Set an object by name
 * $controller->set('helper', 'url', $helper);
 * </code>
 *
 * @param string $type
 */
public function set($type)
{
	// Check number of arguments
	$argc = func_num_args();
	if ($argc == 0)
	{
		throw new InvalidArgumentException('No parameters specified for MVC object');
	}
	if ($argc < 2 || $argc > 3)
	{
		throw new InvalidArgumentException('Invalid parameters specified for MVC object');
	}

	// Process arguments
	$args = func_get_args();
	$obj = array_pop($args);
	$type = array_shift($args);
	if (!preg_match('/^\w+$/', $type))
	{
		throw new InvalidArgumentException('Invalid MVC object type ' . $type);
	}
	if (!empty($args))
	{
		$name = array_shift($args);
		if (!preg_match('/^\w+$/', $name))
		{
			throw new InvalidArgumentException('Invalid MVC object name ' . $name);
		}
	}
	else
	{
		$name = 'default';
	}

	// Store object
	$class_name = $this->getClassName($type, $name);
	$this->objects[$class_name] = $obj;
}

/**
* Return whether an object already exists or not
*
* @param string $type
* @param string $name
* @return bool
*/
public function exists($type, $name = 'default'):bool
{
	$class_name = $this->getClassName($type, $name);
	return isset($this->objects[$class_name]);
}

/**
* Set the name of the variable used for actions
*
* @param string
*/
public function setActionName($name):void
{
	$this->actionName = $name;
}

/**
* Return the name of the variable used for actions
*
* @return string
*/
public function getActionName()
{
	return $this->actionName;
}

/**
 * Return the current action that's been executed
 *
 * @return string
 */
public function getAction()
{
	return (empty($this->currentAction))
		? $this->get('request')->get($this->getActionName())
		: $this->currentAction;
}

/**
 * Return the url for an action
 *
 * Example
 * <code>
 * // Create a link for the current action with a refresh parameter
 * $params = ['refresh'=>1];
 * $url = $controller->getActionUrl(NULL, $params);
 * echo '<a href="', htmlspecialchars($url), '">Refresh</a>';
 *
 * // Create a link for a "delete_color" action with a color of black
 * $params = ['color'=>'black'];
 * $url = $controller->getActionUrl('delete_color', $params);
 * echo '<a href="', htmlspecialchars($url), '">Delete Black</a>';
 * </code>
 *
 * @param string $action the action to return the url for
 * @param array  $params optional associative array of additional parameters
 *                       the for url
 * @return string
 */
public function getActionUrl($action = null, array $params = []):string
{
	// If action is NULL, then use current action
	if (is_null($action))
	{
		$action = $this->getAction();
	}

	// Build and return url
	$params = (isset($action) ? [$this->getActionName()=>$action] : []) + $params;
	return $_SERVER['SCRIPT_NAME'] . (empty($params) ? '' : '?' . http_build_query($params));
}

/**
 * Return the form hidden action tag
 *
 * @param string $action
 * $param array  $params
 * @return string
 */
public function getActionTag($action = null, array $params = []):string
{
	// If action is NULL, then use current action
	if (is_null($action))
	{
		$action = $this->getAction();
	}

	// Build and return HTML
	$html = '';
	foreach (((isset($action) ? [$this->getActionName()=>$action] : []) + $params) as $name => $value)
	{
		$html .= sprintf('<input type="hidden" name="%s" value="%s" />', htmlspecialchars($name), htmlspecialchars($value));
	}
	return $html;
}

/**
 * Load and execute an action
 *
 * @param string  $action the name of the action to execute
 * @throws Exception if action is invalid
 */
public function doAction($action)
{
	// Check action
	if (empty($action) || !is_scalar($action) || !preg_match('/^\w+$/', $action))
	{
		throw new Exception('Invalid action');
	}

	// Execute action
	$action = $this->get('action', $action);
	if (is_object($action))
	{
		$action->execute();
	}
}

//-------------------------------------

/**
 * Run the controller
 */
public function run()
{
	$request = $this->get('request');
	$action = (empty($request->action))
		? $this->defaultAction
		: $request->action;
	$this->doAction($action);
}

/*=======================================================*/
}