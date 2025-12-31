<?php declare(strict_types = 1);

namespace Yau\Mutex\Adapter;

use Yau\Mutex\AdapterInterface;
use InvalidArgumentException;
use RuntimeException;

/**
 * A class used to ensure that only a single process of a script is running
 *
 * This class uses a process id file and file locking to ensure that only a
 * single instance is running. If a process id file does not exist, then one
 * will be automatically created.
 *
 * Example
 * <code>
 * // Load class
 * use Yau\Mutex\Mutex;
 *
 * // Instantiate object
 * $pid_file = '/tmp/myscript.pid';
 * $mutex = Mutex::factory('file', $pid_file);
 *
 * if ($mutex->acquire())
 * {
 *     // Acquired right to process, so begin processing here
 *
 *     // Release right to process
 *     $mutex->release();
 * }
 * </code>
 *
 * Code that uses this module should call release() method when they exit in
 * order to free up the process file.
 *
 * Example of using max_time_func option
 * <code>
 * // Define callback function
 * function notify_admin($seconds)
 * {
 *     $message = 'Processing time was exceeded by ' . $seconds . ' seconds';
 *     mail('admin@mydomain.net', 'Process Error', $message);
 * }
 *
 * // Instantiate object
 * use Yau\Mutex\Mutex;
 * $pid_file = '/tmp/myscript.pid';
 * $options = ['max_time_func'=>'notify_admin'];
 * $mutex = Mutex::factory('file', $pid_file, $options);
 * </code>
 *
 * @author John Yau
 */
class File implements AdapterInterface
{
/*=======================================================*/

/**
 * The process id file
 *
 * @var string
 */
private $pidfile;

/**
 * Associative array of options for process
 *
 * @var array
 */
private $options = [
	'chmod'            => 0664,
	'max_process_time' => 3600,
	'max_time_func'    => null,
	'include_hostname' => true,
];

/**
 * Constructor
 *
 * Example
 * <code>
 * use Yau\Mutex\Mutex;
 *
 * // Instantiate object with max process time of one day
 * $options = ['max_process_time'=>86400];
 * $mutex = Mutex::factory('file', '/tmp/myscript.pid', $options);
 *
 * if ($mutex->acquire())
 * {
 *     // Acquired right to process, so begin processing here
 *
 *     // Release right to process
 *     $mutex->release();
 * }
 * </code>
 *
 * @param string $filename the path to the process id file
 * @param array $options {
 *     @type int $chmod the permission of the process id file to set to; default is 0664
 *     @type int $max_process_time the maximum time for a process in seconds. If a process exceeds this time, then it will be killed. The default is one hour.
 *     @type mixed $max_time_func the callback function to call when a process exceeds the maximum time
 *     @type bool $include_hostname include host name in pid file
 * }
 * @throws InvalidArgumentException if there's an error with the arguments
 * @throws RuntimeException if file or directory isn't writable
 */
public function __construct($filename, array $options = [])
{
	// Check filename
	if (empty($filename))
	{
		throw new InvalidArgumentException('Empty process id file');
	}

	// Check that file or directory is writable
	if (file_exists($filename))
	{
		// Check that file id writable
		if (!is_writable($filename))
		{
			throw new RuntimeException('Process file ' . $filename . ' is not writable');
		}
	}
	else
	{
		// Check that directory writable
		$filedir = dirname($filename);
		if (!is_writable($filedir))
		{
			throw new RuntimeException('Directory ' . $filedir . ' is not writable');
		}
	}

	// Check whether callback function is callable or not
	if (!empty($options['max_time_func'])
		&& !is_callable($options['max_time_func']))
	{
		throw new InvalidArgumentException('Callback function is not callable');
	}

	// Store the current process id and filename
	$this->pidfile = $filename;

	// Store process options
	if (!empty($options))
	{
		$this->options = array_merge($this->options, $options);
	}

}

/**
 * Return the file key for the current process to store
 *
 * @return string
 */
private function getMyKey(): string
{
	return strval(getmypid()) . ($this->options['include_hostname'] ? '|' . gethostname() : '');
}

/**
 * Check whether file key matches current process and host
 *
 * @param string $value
 * @param array $matches reference to variable to hold matches
 * @return bool
 */
private function isMyKey($value, &$matches = []): bool
{
	return (preg_match('/^(\d{1,20})(?:\|(.+))?$/', $value, $matches)
		&& $matches[1] == getmypid()
		&& (!isset($matches[2]) || strcmp($matches[2], gethostname()) == 0));
}

/**
 * Acquire the right to begin processing
 *
 * This acquires the right to process by writing the current process id to
 * a process file that was defined in the constructor.
 *
 * @return bool true if acquisition was successful, otherwise false
 */
public function acquire(): bool
{
	clearstatcache();

	// If pid file does not exist, then create one
	if (!file_exists($this->pidfile))
	{
		// Varible to store result
		$result = false;

		// Open process file
		if ($handle = fopen($this->pidfile, 'x'))
		{
			// Write out pid to file
			if (flock($handle, LOCK_EX + LOCK_NB, $wouldblock))
			{
				fwrite($handle, $this->getMyKey());
				flock($handle, LOCK_UN);
				$result = true;
			}
			fclose($handle);

			// Change permission of file
			if (!empty($this->options['chmod']))
			{
				chmod($this->pidfile, $this->options['chmod']);
			}
		}

		// Return result
		return $result;
	}

	// Open up process file
	if ($handle = fopen($this->pidfile, 'r+'))
	{
		// Try to obtain lock file without blocking
		if (flock($handle, LOCK_EX + LOCK_NB, $wouldblock))
		{
			// Read file
			$contents = fread($handle, 1014);

			// If key is for the current process, then return true
			if ($this->isMyKey($contents, $matches))
			{
				flock($handle, LOCK_UN);
				fclose($handle);
				return true;
			}
			$file_pid = intval($matches[1] ?? 0);
			$file_host = $matches[2] ?? gethostname();

			// Check whether file can be overwritten
			$overwrite = false;
			$exceeded_time = false;
			if (empty($contents))
			{
				// Blank file
				$overwrite = true;
			}
			elseif (!empty($file_pid)
				&& ($hostname = gethostname())
				&& strcmp($file_host, $hostname) == 0)
			{
				// Host matches
				if (posix_getsid($file_pid) === false)
				{
					// Process id no longer exists
					$overwrite = true;
				}
				elseif (($max_process_time = $this->options['max_process_time']) > 0
					&& time() - filemtime($this->pidfile) > $max_process_time
					&& posix_kill($file_pid, 0)
					&& posix_kill($file_pid, SIGTERM))
				{
					// Process exceeds max process time and killed
					$overwrite = true;
					$exceeded_time = true;
				}
			}

			// Overwrite file
			if ($overwrite)
			{
				// Truncate file
				ftruncate($handle, 0);
				fseek($handle, 0);

				// Write new key to file
				fwrite($handle, $this->getMyKey());
				flock($handle, LOCK_UN);
				fclose($handle);

				// Call function if exceeded maximum process time
				if ($exceeded_time && !empty($this->options['max_time_func']))
				{
					call_user_func($this->options['max_time_func'], $exceeded_time);
				}

				// Return true
				return true;
			}

			// Remove file lock
			flock($handle, LOCK_UN);
		}

		// Close file
		fclose($handle);
	}

	// Return false to indicate acquisition failed
	return false;
}

/**
* Truncate process file to indicate that processing is done
*
* @return bool true if process file was successfully released, or false if not
*/
public function release(): bool
{
	// Return true if process file does not exist
	if (!file_exists($this->pidfile))
	{
		return true;
	}

	// Variable to store release result
	$result = false;

	// Truncate pid file
	if ($handle = fopen($this->pidfile, 'r+'))
	{
		if (flock($handle, LOCK_EX + LOCK_NB, $wouldblock))
		{
			$pid = fread($handle, 1014);

			// Truncate file if pid matches current pid
			if ($this->isMyKey($pid))
			{
				ftruncate($handle, 0);
				$result = true;
			}

			// Unlock file and remove file
			if (flock($handle, LOCK_UN) && $result)
			{
				unlink($this->pidfile);
			}
		}
		fclose($handle);
	}

	// Return result
	return $result;
}

/**
 * Update the access and modification time for the current process file
 *
 * @return bool true if update was successful, or false if not
 */
public function keepAlive(): bool
{
	// Variable to store result
	$result = false;

	// Open file and check that it has the current pid
	if ($handle = fopen($this->pidfile, 'r'))
	{
		if (flock($handle, LOCK_EX + LOCK_NB, $wouldblock))
		{
			$pid = fread($handle, 1014);

			// If pid matches current pid, then touch it
			if ($this->isMyKey($pid))
			{
				$result = touch($this->pidfile, time());
			}
			flock($handle, LOCK_UN);
		}
		fclose($handle);
	}

	// Return result
	return $result;
}

/*=======================================================*/
}
