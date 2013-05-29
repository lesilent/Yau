<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_Mutex
*/

namespace Yau\Mutex;

/**
* Interface classes used to ensure that only a single instance is running
*
* @author   John Yau
* @category Yau
* @package  Yau_Mutex
*/
interface AdapterInterface
{
/*=======================================================*/

/**
* Acquire the right to begin processing
*
* @return boolean TRUE if acquisition was successful, otherwise FALSE
* @throws Exception if process id file cannot be created
*/
public function acquire();

/**
* Truncate process file to indicate that processing is done
*
* @return boolean TRUE if process was successfully released, or FALSE if not
*/
public function release();

/**
* Update mutex with keep alive signal to indicate that script is still running
*
* This is used to inform other processes that script is still running properly
* and to not terminate/kill it if it exceeds the maximum allowed execution
* time.
*/
public function keepAlive();

/*=======================================================*/
}
