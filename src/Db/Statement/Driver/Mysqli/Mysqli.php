<?php declare(strict_types = 1);

namespace Yau\Db\Statement\Driver\Mysqli;

use Yau\Db\Statement\Driver\AbstractDriver;
use Yau\Db\Adapter\Driver\Mysqli\Mysqli as Adapter;
use RuntimeException;

/**
 * Statement class for use with mysqli objects
 *
 * @author John Yau
 */
class Mysqli extends AbstractDriver
{
/*=======================================================*/

/**
 * The original statement to prepare
 *
 * @var string
 */
protected $stmt;

/**
 * Prepare statement and store it
 *
 * @param string $stmt the SQL statement to prepare
 * @throws RuntimeException if error preparing statement
 */
protected function prepare($stmt)
{
	$this->sth = $this->dbh->prepare($stmt);
	if ($this->sth === false)
	{
		throw new RuntimeException('Error preparing statement: ' . $stmt);
	}
}

/**
 * Execute prepared statement with some values
 *
 * @param array $params array of values to bind to statement
 * @return bool true if successful, or false on failure
 * @throws RuntimeException if error executing statement
 * @uses Adapter::buildBindString()
 */
public function execute(array $params = [])
{
	// Free previous result, if any
	$this->freeResult();

	// Bind parameters to prepared statement
	$types = Adapter::buildBindString($params);
	array_unshift($params, $types);
	$result = call_user_func_array(array($this->sth, 'bind_param'), $params);
	if (empty($result))
	{
		return false;
	}

	// Execute statement and return result
	$result = $this->sth->execute();
	if ($result === false)
	{
		throw new RuntimeException($this->sth->error, $this->sth->errno);
	}
	return $result;
}

/**
 * Fetch a single row from result set as an associative array
 *
 * @return mixed a row from the result set, or FALSE if there are no more
 */
public function fetchAssocRow()
{
	$this->res->fetch_assoc();
}

/**
 * Fetch a single row from result set as a numeric array
 *
 * @return mixed a row from the result set, or FALSE if there are no more
 */
public function fetchNumRow()
{
	$this->res->fetch_array();
}

/**
 * Free and release the current result set
 *
 * @return bool true on success, or false on failure
 */
public function freeResult()
{
	// Free result
	$result = true;
	if (isset($this->res))
	{
		$result = $this->res->free_result();
		unset($this->res);
	}

	// Free statement
	if (!empty($this->sth))
	{
		$this->sth->close();
	}

	// Return result
	return $result;
}

/**
 * Return the number of rows in the result set
 *
 * @return int the number of rows in the result set, or FALSE on failure
 */
public function numRows()
{
	return (empty($this->res)) ? false : $this->res->num_rows;
}

/*=======================================================*/
}