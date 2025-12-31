<?php declare(strict_types = 1);

namespace Yau\Mutex;

/**
 * Interface class for mutex adapters
 *
 * @author John Yau
 */
interface AdapterInterface
{
/*=======================================================*/

/**
 * Acquire the right to begin processing
 *
 * @return bool true if acquisition was successful, otherwise false
 */
public function acquire(): bool;

/**
 * Truncate process file to indicate that processing is done
 *
 * @return bool true if process was successfully released, or false if not
 */
public function release(): bool;

/**
 * Update mutex with keep alive signal to indicate that script is still running
 *
 * This is used to inform other processes that script is still running properly
 * and to not terminate/kill it if it exceeds the maximum allowed execution
 * time.
 *
 * @return bool
 */
public function keepAlive(): bool;

/*=======================================================*/
}
