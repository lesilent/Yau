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
* @author   John Yau
* @category Yau
* @package  Yau_Mutex
*/
class Semaphore extends MutexInterface
{
/*=======================================================*/

/**
* The semaphore identifier
*
* @var resource
*/
private $sem;

/**
* Constructor
*
* @param  mixed  $sem_identifier a semaphore id
*/
public function __construct($sem_identifier)
{
	$this->sem = $sem_identifier;
}

/**
* Acquire the right to begin processing
*
* This acquires the right to process by writing the current process id to
* a process file that was defined in the constructor.
*
* @return boolean TRUE if acquisition was successful, otherwise FALSE
* @throws Exception
*/
public function acquire()
{
	return sem_acquire($this->sem);
}

/**
* Truncate process file to indicate that processing is done
*
* @return boolean TRUE if process file was successfully released, or FALSE if
*                 not
*/
public function release()
{
	return sem_release($this->sem);
}

/**
* Update timestamp in record to indicate script is still running properly
*
* @return boolean this always returns TRUE since this isn't implemented
*/
public function keepAlive()
{
	return TRUE;
}

/*=======================================================*/
}

