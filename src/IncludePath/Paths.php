<?php declare(strict_types = 1);

namespace Yau\IncludePath;

/**
 * Class for handling a custom set of include paths
 *
 * Example
 * <code>
 * use Yau\IncludePath\Paths;
 *
 * // Instantiate object
 * $dirs = ['/home/users/john/include', '/home/users/jane/include'];
 * $paths = new Paths($dirs);
 *
 * // Include a file
 * $paths->includeFile('page.inc');
 * </code>
 *
 * @author John Yau
 */
class Paths
{
/*=======================================================*/

/**
 * The file paths for the object
 *
 * @var array
 */
protected $paths = [];

/**
 * Constructor for returning an include path object
 *
 * @param mixed $paths either an array of paths, or the include path string to search for file
 * @param bool $use_include_path true to also add the paths from the include path
 */
public function __construct($paths = [], bool $use_include_path = false)
{
	// Add paths
	if (!empty($paths))
	{
		if (is_string($paths))
		{
			$paths = explode(PATH_SEPARATOR, $paths);
		}
		foreach ($paths as $path)
		{
			$this->addPath($path);
		}
	}

	// Store paths from the current include path
	if ($use_include_path)
	{
		foreach (explode(PATH_SEPARATOR, get_include_path()) as $path)
		{
			$this->addPath($path);
		}
	}
}

/**
 * Add a path to the current list of include paths
 *
 * Example
 * <code>
 * use Yau\IncludePath\IncludePath';
 *
 * // Instantiate object
 * $dirs = ['/home/mysite/templates', '/users/john/templates'];
 * $paths = new IncludePath($dirs);
 *
 * // Add additional path
 * $paths->addPath('/home/jane/templates');
 * </code>
 *
 * @param string $path the path to add
 */
public function addPath(string $path)
{
	$this->paths[rtrim($path, DIRECTORY_SEPARATOR)] = true;
}

/**
 * Remove a path from the list of include paths
 *
 * Example
 * <code>
 * use Yau\IncludePath\IncludePath';
 *
 * // Instantiate object
 * $dirs = ['/home/mysite/templates', '/users/john/templates'];
 * $paths = new IncludePath($paths);
 *
 * // Remove added path
 * $paths->removePath('/home/mysite/templates');
 * </code>
 *
 * @param string $path the path to remove
 */
public function removePath(string $path)
{
	unset($this->paths[$path]);
}

/**
 * Return an array of the the current paths
 *
 * Example
 * <code>
 * use Yau\IncludePath\IncludePath';
 *
 * // Instantiate object
 * $paths = ['/home/mysite/templates', '/users/john/templates'];
 * $incpath = new IncludePath($paths);
 *
 * $incpath->addPath('/users/jane/templates');
 * $paths = $incpath->getPaths();
 * print_r($paths);
 * </code>
 *
 * The above example will output:
 * <code>
 * Array
 * (
 *     [0] => /home/mysite/templates
 *     [1] => /users/john/templates
 *     [2] => /users/jane/templates
 * )
 * </code>
 *
 * @return array the current include paths in the object
 */
public function getPaths(): array
{
	return array_keys($this->paths);
}

/**
 * Return whether a file exists in path
 *
 * @param string $filename
 * @return bool
 */
public function fileExists(string $filename): bool
{
	return (($filename = $this->findFile($filename)) !== false);
}

/**
 * Include a file from paths in object
 *
 * Example
 * <code>
 * use Yau\IncludePath\IncludePath';
 *
 * $incpath = new IncludePath('/home/users/john', 'home/users/jane');
 * $incpath->includeFile('templates/page.inc');
 * </code>
 *
 * @param string $filename the file to include
 * @return mixed the return value from the include file, or false if error
 */
public function includeFile(string $filename)
{
	return (($filename = $this->findFile($filename)) !== false)
		? include($filename)
		: false;
}

/**
 * Require a file from include paths in object
 *
 * Example
 * <code>
 * use Yau\IncludePath\IncludePath';
 *
 * $incpath = new IncludePath('/home/users/john', 'home/users/jane');
 * $incpath->requireFile('templates/page.inc');
 * </code>
 *
 * @param string $filename the file to require
 * @return mixed the return value from the required file, or false if error
 */
public function requireFile(string $filename)
{
	return (($filename = $this->findFile($filename)) !== false)
		? require($filename)
		: false;
}

/**
 * Include a file once from paths in object
 *
 * Example
 * <code>
 * use Yau\IncludePath\IncludePath';
 *
 * $incpath = new IncludePath('/home/users/john', 'home/users/jane');
 * $incpath->includeOnceFile('templates/page.inc');
 * </code>
 *
 * @param string $filename the file to include once
 * @return mixed the return value from the include file, or false if error
 */
public function includeOnceFile(string $filename)
{
	return (($filename = $this->findFile($filename)) !== false)
		? include_once($filename)
		: false;
}

/**
 * Require a file once from paths in object
 *
 * Example
 * <code>
 * use Yau\IncludePath\IncludePath';
 *
 * $incpath = new IncludePath('/home/users/john', 'home/users/jane');
 * $incpath->requireOnceFile('templates/page.inc');
 * </code>
 *
 * @param string $filename the file to require once
 * @return mixed the return value from the required file, or false if error
 */
public function requireOnceFile(string $filename)
{
	return (($filename = $this->findFile($filename)) !== false)
		? require_once($filename)
		: false;
}

/**
 * Find a file among the current paths
 *
 * @uses IncludePath::getFullPath()
 */
private function findFile(string $filename)
{
	return self::getFullPath($filename, $this->getPaths());
}

/**
* Return the current paths as a string suitable for set_include_path()
*
* Example
* <code>
* // Instantiate object
* use Yau\IncludePath\IncludePath';
* $paths = new IncludePath();
*
* // Add paths
* $paths->addPath('/home/users/john/include');
* $paths->addPath('/home/users/jane/include');
*
* echo $paths;
* </code>
*
* @return string
*/
public function __toString(): string
{
	return implode(PATH_SEPARATOR, array_keys($this->paths));
}

//-------------------------------------
// Static methods

/**
 * Add a path to the current include paths
 *
 * Example
 * <code>
 * use Yau\IncludePath\IncludePath';
 *
 * IncludePath::add('/home/john/include');
 * </code>
 *
 * @param string $path the path to add to the current include path
 * @return string the previous include path or false on failure
 */
public static function add(string $path): string
{
	// Add path to current include paths
	$incpath = get_include_path();
	$paths = explode(PATH_SEPARATOR, $incpath);
	if (in_array($path, $paths))
	{
		// Path is already among include paths, so return current path
		return $incpath;
	}

	// Add new path to paths array
	$paths[] = $path;

	// Set new include path
	return set_include_path(implode(PATH_SEPARATOR, $paths));
}

/**
 * Remove a path from the current include paths
 *
 * Example
 * <code>
 * use Yau\IncludePath\IncludePath';
 *
 * IncludePath::remove('/home/john/include');
 * </code>
 *
 * @param string $path the path to remove from the current include path
 * @return string the previous include path or false on failure
 */
public static function remove(string $path): string
{
	// Add path to current include paths
	$incpath = get_include_path();
	$paths = explode(PATH_SEPARATOR, $incpath);
	if (($key = array_search($path, $paths)) === false)
	{
		// Path is not in include path, so return current path
		return $incpath;
	}

	// Remove path from paths array
	unset($paths[$key]);

	// Set new include path
	return set_include_path(implode(PATH_SEPARATOR, $paths));
}

/**
 * Find a file among an array paths and return its absolute path
 *
 * Example
 * <code>
 * use Yau\IncludePath\IncludePath';
 *
 * // Find the real path for "myfile.php" among the include paths
 * $paths = IncludePath::getFullPath('myfile.php');
 * </code>
 *
 * @param string $filename the file to the find the path for
 * @param array|null $paths array of paths to search for file; if omitted,
 *                     then the current include path will be searched
 * @return string|false the absolute path to the file, or false if not found
 */
public static function getFullPath(string $filename, ?array $paths = null)
{
	// If first character is directory separator, then absolute path
	if ($filename[0] == DIRECTORY_SEPARATOR)
	{
		return (file_exists($filename)) ? realpath($filename) : false;
	}

	// If no paths are passed, then use current include paths
	if (is_null($paths))
	{
		$paths = explode(PATH_SEPARATOR, get_include_path());
	}

	// Search paths
	foreach ($paths as $path)
	{
		$fullpath = $path . DIRECTORY_SEPARATOR . $filename;
		if (file_exists($fullpath))
		{
			return realpath($path . PATH_SEPARATOR . $filename);
		}
	}

	// Return false if file is not found
	return false;
}

/*=======================================================*/
}
