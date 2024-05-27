<?php declare(strict_types = 1);

namespace Yau\ActionMVC;

use Yau\ActionMVC\ObjectTrait;
use Exception;

/**
 * Router
 */
class Router
{
use ObjectTrait;
/*=================================================================*/

/**
 * Pattern for slugs
 *
 * @var string
 */
private static $SLUG_PATTERN = '/\{(\w+)\}/';

/**
 * Routes
 *
 * @var array
 */
private $routes = [];

/**
 * Add an action route
 *
 * @param string $route
 * @param array $slugs
 * @param string $action optional action if different than one defined in route
 * @throws Exception
 */
public function addRoute($route, array $slugs, $action = null)
{
	$holders = [];
	$pattern = ((strcmp($route[0], '^') == 0) ? '^' : '')
		. preg_replace_callback(self::$SLUG_PATTERN, function ($matches) use (&$holders, $slugs) {
		if (!isset($slugs[$matches[1]]))
		{
			throw new Exception('No slug defined for ' . $matches[1]);
		}
		$holders[] = $matches[1];
		return '(' . preg_replace('#(?<!\\\)\((?!\?)#', '(?:', $slugs[$matches[1]]) . ')';
	}, $route) . ((strcmp($pattern[strlen($pattern) - 1], '$') != 0) ? '(?=[\/\?\#]|$)' : '');
	if (empty($action) && preg_match('/\w+/', $route, $matches))
	{
		$action = $matches[0];
	}
	$this->routes[$route] = [
		'action'  => $action,
		'route'   => $route,
		'pattern' => $pattern,
		'slugs'   => $slugs,
		'holders' => $holders,
	];
}

/**
 * Match a path to a route
 *
 * @param string $path
 * @return array|false
 */
public function match($path)
{
	foreach ($this->routes as $route)
	{
		if (preg_match('#^' . $route['pattern'] . '(?=[\/\?\#]|$)#', $path, $matches))
		{
			$action_name = ($controller = $this->getController()) ? $controller->getActionName() : 'action';
			$params = [$action_name=>$route['action']];
			foreach ($route['holders'] as $offset => $slug)
			{
				$params[$slug] = $matches[$offset + 1];
			}
			return $params;
		}
	}
	return false;
}

/**
 * Return the path
 *
 * @param string $action
 * @param array $params
 * @return string|false
 */
public function getUrlPath($action, array $params = [])
{
	foreach ($this->routes as $route)
	{
		if (strcmp($route['action'], $action) == 0
			&& count(array_diff_key($route['slugs'], $params)) == 0)
		{
			$path = preg_replace_callback(self::$SLUG_PATTERN, fn(array $matches) => $params[$matches[1]] ?? '', $route['route']);
			if ($data = array_diff_key($params, $route['slugs']))
			{
				$path .= '?' . http_build_query($data);
			}
			return $path;
		}
	}
	return false;
}

/*=================================================================*/
}
