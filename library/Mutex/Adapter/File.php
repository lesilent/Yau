<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_Mutex
*/

namespace Yau\Mutex\Adapter;

use Yau\Mutex\AdapterInterface;

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
* $options = array('max_time_func'=>'notify_admin');
* $mutex = Mutex::factory('file', $pid_file, $options);
* </code>
*
* @author   John Yau
* @category Yau
* @package  Yau_Mutex
*/
class File extends AdapterInterface
{
/*=======================================================*/

/**
* The process id file
*
* @var string
*/
private $pidfile;

/**
* The current process id
*
* @var string
*/
private $mypid;

/**
* Associative array of options for process
*
* Defaults:
* <pre>
* - chmod            integer the permission of the internal pid file for the
*                            class when it's initially created. The default is
*                            0664.
* - max_process_time integer the maximum time for a process in seconds. The
*                            default is 3600, or one hour.
* - max_time_func    mixed   the callback function to call when a process
*                            exceeds the maximum process time. The number of
*                            seconds that was exceeded will be passed to the
*                            function.
* </pre>
*
* @var options
*/
private $options = array(
	'chmod'            => 0664,
	'max_process_time' => 3600,
	'max_time_func'    => NULL,
);

/**
* Constructor
*
* Example
* <code>
* require_once 'Util.php';
* Util::loadClass('Util_Mutex_File');
*
* // Instantiate object with max process time of one day
* $options = array('max_process_time'=>86400);
* $mutex = new Util_Mutex_File('/tmp/myscript.pid', $options);
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
* Options:
* <pre>
* - chmod            integer the permission of the process id file to set to;
*                            default is 0664
* - max_process_time integer the maximum time for a process in seconds. If a
*                            process exceeds this time, then it will be killed.
*                            The default is one hour.
* - max_time_func    mixed   the callback function to call when a process
*                            exceeds the maximum time
* </pre>
*
* @param  string $filename the path to the process id file
* @param  array  $options  optional associative array of options
* @throws Exception if there's an error with the arguments
*/
public function __construct($filename, array $options = array())
{
	// Store the current process id
	$this->mypid = getmypid();

	// Store process id file
	if (empty($filename))
	{
		throw new Exception('Empty process id file');
	}
	$this->pidfile = $filename;

	// Check that file or directory is writable
	if (file_exists($filename))
	{
		// Check that file id writable
		if (!is_writable($filename))
		{
			throw new Exception('Process file ' . $this->pidfile
				. ' is not writable');
		}
	}
	else
	{
		// Check that directory writable
		$filedir = dirname($filename);
		if (!is_writable($filedir))
		{
			throw new Exception('Directory ' . $filedir
				. ' is not writable');
		}
	}

	// Store process options
	if (!empty($options))
	{
		$this->options = array_merge($this->options, $options);
	}

	// Check whether callback function is callable or not
	if (!empty($this->options['max_time_func'])
		&& !is_callable($this->options['max_time_func']))
	{
		throw new Exception('Callback function is not callable');
	}
}

/**
* Acquire the right to begin processing
*
* This acquires the right to process by writing the current process id to
* a process file that was defined in the constructor.
*
* @return boolean TRUE if acquisition was successful, otherwise FALSE
* @throws Exception if process id file cannot be created
*/
public function acquire()
{
	clearstatcache();

	// If pid file does not exist, then create one
	if (!file_exists($this->pidfile))
	{
		// Varible to store result
		$result = FALSE;

		// Open process file
		if ($handle = fopen($this->pidfile, 'x'))
		{
			// Write out pid to file
			if (flock($handle, LOCK_EX + LOCK_NB, $wouldblock))
			{
				fwrite($handle, $this->mypid);
				flock($handle, LOCK_UN);
				$result = TRUE;
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
			// Read pid in file
			$pid = intval(fread($handle, 1014));

			// If pid is the current process, then return TRUE
			if ($pid == $this->mypid)
			{
				flock($handle, LOCK_UN);
				fclose($handle);
				return TRUE;
			}

			// Store the number of seconds process exceeded the maximum
			$max_process_time = $this->options['max_process_time'];
			$exceeded_time = (empty($max_process_time))
				? 0
				: time() - filemtime($this->pidfile) - $max_process_time;

			// Define value for SIGTERM
			if (!defined('SIGTERM'))
			{
				define('SIGTERM', 15);
			}

			// Write out pid to file if any of the following are true
			// 1. no pid in file (usually because file is blank)
			// 2. process is not currently running
			// 3. file exceeds max_process_time and process has been killed
			if (empty($pid)
				|| (($sid = posix_getsid($pid)) === FALSE)
				|| ($exceeded_time > 0 && posix_kill($pid, 0) && posix_kill($pid, SIGTERM))
			)
			{
				// Truncate file
				ftruncate($handle, 0);
				fseek($handle, 0);

				// Write new pid to file
				fwrite($handle, $this->mypid);
				flock($handle, LOCK_UN);
				fclose($handle);

				// Call function if exceeded maximum process time
				if ($exceeded_time > 0
					&& !empty($this->options['max_time_func']))
				{
					call_user_func($this->options['max_time_func'], $exceeded_time);
				}

				// Return TRUE
				return TRUE;
			}

			// Remove file lock
			flock($handle, LOCK_UN);
		}

		// Close file
		fclose($handle);
	}

	// Return FALSE to indicate acquisition failed
	return FALSE;
}

/**
* Truncate process file to indicate that processing is done
*
* @return boolean TRUE if process file was successfully released, or FALSE if
*                 not
*/
public function release()
{
	// Return TRUE if process file does not exist
	if (!file_exists($this->pidfile))
	{
		return TRUE;
	}

	// Variable to store release result
	$result = FALSE;

	// Truncate pid file
	if ($handle = fopen($this->pidfile, 'r+'))
	{
		if (flock($handle, LOCK_EX + LOCK_NB, $wouldblock))
		{
			$pid = intval(fread($handle, 1014));

			// Truncate file if pid matches current pid
			if ($pid == $this->mypid)
			{
				ftruncate($handle, 0);
				$result = TRUE;
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
* @return boolean TRUE if update was successful, or FALSE if not
*/
public function keepAlive()
{
	// Variable to store result
	$result = FALSE;

	// Open file and check that it has the current pid
	if ($handle = fopen($this->pidfile, 'r'))
	{
		if (flock($handle, LOCK_EX + LOCK_NB, $wouldblock))
		{
			$pid = intval(fread($handle, 1014));

			// If pid matches current pid, then touch it
			if ($pid == $this->mypid)
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
