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
 * Flag for returning path with trailing slash
 *
 * @var bool default is false
 * @link https://developers.google.com/search/blog/2010/04/to-slash-or-not-to-slash
 */
private $slash = false;

/**
 * Add an action route
 *
 * @param string $route
 * @param array $slugs
 * @param string $action optional action if different than one defined in route
 * @throws Exception
 */
public function addRoute(string $route, array $slugs, $action = null)
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
	}, $route) . ((($length = strlen($route)) > 1 && strcmp($route[$length - 1], '$') != 0) ? '(?=[\/\?\#]|$)' : '');
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
public function match(?string $path)
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

			// Append query parameters if any
			$query = parse_url($path, PHP_URL_QUERY);
			if (is_string($query) && strlen($query) > 0)
			{
				parse_str($query, $result);
				$params += $result;
			}

			// Return parameters
			return $params;
		}
	}
	return false;
}

/**
 * Return flag for returning path with trailing slash
 *
 * @return bool
 */
public function getTrailingSlash():bool
{
	return $this->slash;
}

/**
 * Set flag for returning path with trailing slash
 *
 * @param bool $slash
 */
public function setTrailingSlash(bool $slash):void
{
	$this->slash = $slash;
}

/**
 * Return the corresponding route path based on the action and parameters
 *
 * @param string $action
 * @param array $params
 * @return string|false
 */
public function getPath($action, array $params = [])
{
	foreach ($this->routes as $route)
	{
		if (strcmp($route['action'], $action) == 0
			&& count(array_diff_key($route['slugs'], $params)) == 0)
		{
			$path = preg_replace_callback(self::$SLUG_PATTERN, fn(array $matches) => $params[$matches[1]] ?? '', $route['route']);
			if ($this->getTrailingSlash() && strcmp(substr($path, -1), '/') != 0)
			{
				$path .= '/';
			}
			if ($data = array_diff_key($params, $route['slugs']))
			{
				$path .= ((strpos($path, '?') === false) ? '?' : '&')
					. http_build_query($data);
			}
			return $path;
		}
	}
	return false;
}

/*=================================================================*/
}
