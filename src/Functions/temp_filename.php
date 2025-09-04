<?php declare(strict_types = 1);

namespace Yau\Functions;

/**
 * Create a file with a unique filename and register function to remove upon shutdown
 *
 * @param string $prefix the optional prefix name
 * @param string $directory optional directory if other than path used for temporary files
 * @return string|false
 */
function temp_filename(string $prefix = 'yau', ?string $directory = null)
{
	if (($fname = tempnam(empty($directory) ? sys_get_temp_dir() : $directory, $prefix)) !== false)
	{
		register_shutdown_function(fn() => file_exists($fname) && unlink($fname));
	}
	return $fname;
}
