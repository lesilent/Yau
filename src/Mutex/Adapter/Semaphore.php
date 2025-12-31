<?php declare(strict_types = 1);

namespace Yau\Mutex\Adapter;

use Yau\Mutex\AdapterInterface;

/**
* A class used to ensure that only a single process of a script is running
*
* @author John Yau
*/
class Semaphore implements AdapterInterface
{
/*=======================================================*/

/**
 * The semaphore identifier
 *
 * @var resource|object
 */
private $semaphore;

/**
 * Constructor
 *
 * @param $semaphore|object $semaphore a semaphore resource or object
 */
public function __construct($semaphore)
{
	$this->semaphore = $semaphore;
}

/**
 * Acquire the right to begin processing
 *
 * @return bool true if acquisition was successful, otherwise false
 */
public function acquire(): bool
{
	return sem_acquire($this->semaphore, true);
}

/**
 * Truncate process file to indicate that processing is done
 *
 * @return bool true if process file was successfully released, or false if not
 */
public function release(): bool
{
	return sem_release($this->semaphore);
}

/**
 * Update timestamp in record to indicate script is still running properly
 *
 * @return bool this always returns true since this isn't implemented
 */
public function keepAlive(): bool
{
	return true;
}

/*=======================================================*/
}

