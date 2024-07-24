<?php declare(strict_types = 1);

namespace Yau\IncludePath;

/**
 * Class to manipulate PHP's or create custom include paths
 *
 * Example of adding a path to the current include path
 * <code>
 * use Yau\IncludePath\IncludePath;
 *
 * // Add a path to the include path
 * IncludePath::add('/home/users/john/include');
 *
 * include 'myfile.php';
 *
 * // Remove path from the include path
 * IncludePath::remove('/home/users/john/include');
 * </code>
 *
 * @author John Yau
 */
class IncludePath
{
/*=======================================================*/

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
public static function add(string $path):string
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
public static function remove(string $path):string
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
 * @param array  $paths    array of paths to search for file; if omitted,
 *                         then the current include path will be searched
 * @return string|false the absolute path to the file, or false if not found
 */
public static function getFullPath(string $filename, $paths = null)
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
