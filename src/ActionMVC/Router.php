<?php declare(strict_types = 1);

namespace Yau\ActionMVC;

use Yau\ActionMVC\ObjectTrait;
use InvalidArgumentException;

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
private $trailingSlash = false;

/**
 * Flag for whether default paths should be returned if no matching route is found
 *
 * @var bool default is false
 */
private $defaultPaths = false;

/**
 * Add an action route
 *
 * @param string $route
 * @param array  $slugs
 * @param string $action optional action if different than one defined in route
 * @throws InvalidArgumentException if invalid route or missing slugs
 */
public function addRoute(string $route, array $slugs, ?string $action = null)
{
	if (strlen($route) < 1)
	{
		throw new InvalidArgumentException('Invalid route');
	}
	$holders = [];
	$pattern = ((strcmp($route[0], '^') == 0) ? '^' : '')
		. preg_replace_callback(self::$SLUG_PATTERN, function ($matches) use (&$holders, $slugs) {
		if (!isset($slugs[$matches[1]]))
		{
			throw new InvalidArgumentException('No slug defined for ' . $matches[1]);
		}
		$holders[] = $matches[1];
		return '(' . preg_replace('#(?<!\\\)\((?!\?)#', '(?:', $slugs[$matches[1]]) . ')';
	}, str_replace('.', '\\.' ,$route));
	$pos = strlen($pattern) - 1;
	$last_char = $pattern[$pos];
	if ($last_char === '$')
	{
		$pattern = substr($pattern, 0, $pos) . '(?=[\?\#]|$)';
		$route_val = substr($route, 0, -1);
	}
	else
	{
		$pattern .= ($last_char === '/') ? '(?=[\?\#]|$)' : '(?=[\/\?\#]|$)';
		$route_val = $route;
	}
	if (empty($action) && preg_match('/[a-z]\w*/', $route, $matches))
	{
		$action = $matches[0];
	}
	$this->routes[$route] = [
		'action'  => $action,
		'route'   => $route_val,
		'pattern' => $pattern,
		'slugs'   => $slugs,
		'holders' => $holders,
	];
}

/**
 * Match a path to an existing route
 *
 * @param string $path
 * @return array|false
 */
public function match(?string $path)
{
	foreach ($this->routes as $route)
	{
		if (preg_match('#^' . $route['pattern'] . '#', $path, $matches))
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
 * Return or set whether returning path with trailing slash
 *
 * @param bool $slash
 * @return bool the current setting
 */
public function useTrailingSlash(?bool $slash = null):bool
{
	$result = $this->trailingSlash;
	if (isset($slash))
	{
		$this->trailingSlash = $slash;
	}
	return $result;
}

/**
 * Return or set whether default paths should be returned if no route is matched
 *
 * @param bool $default
 * @return bool the current setting
 */
public function useDefaultPaths(?bool $default = null):bool
{
	$result = $this->defaultPaths;
	if (isset($default))
	{
		$this->defaultPaths = $default;
	}
	return $result;
}

/**
 * Return the corresponding route path based on the action and parameters
 *
 * @param string $action
 * @param array $params
 * @return string|false
 */
public function getPath(string $action, array $params = [])
{
	$path = false;
	foreach ($this->routes as $route)
	{
		if (strcmp($route['action'], $action) == 0
			&& count(array_diff_key($route['slugs'], $params)) == 0)
		{
			$path = preg_replace_callback(self::$SLUG_PATTERN, fn(array $matches) => $params[$matches[1]] ?? '', $route['route']);
			if ($this->useTrailingSlash() && strcmp(substr($path, -1), '/') != 0)
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
	if ($this->useDefaultPaths())
	{
		$path = '/' . preg_replace('/_+/', '/', $action)
			. ($this->useTrailingSlash() ? '/' : '')
			. (empty($params) ? '' : '?' . http_build_query($params));
	}
	return $path;
}

/*=================================================================*/
}
