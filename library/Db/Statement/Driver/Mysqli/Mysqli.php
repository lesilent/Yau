<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
*/

namespace Yau\Db\Statement\Driver\Mysqli;

use Yau\Db\Statement\Driver\AbstractDriver;
use Yau\Db\Statement\Exception\RuntimeException;
use Yau\Db\Adapter\Driver\Mysqli\Mysqli as Adapter;


/**
* Statement class for use with mysqli objects
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
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
* Map of fetch modes to fetch methods
*
* @var array
*/
protected static $FETCH_METHODS = array(
	Util_DB::FETCH_ASSOC  => 'fetch_assoc',
	Util_DB::FETCH_BOTH   => 'fetch_array',
	Util_DB::FETCH_NUM    => 'fetch_row',
	Util_DB::FETCH_OBJECT => 'fetch_object'
);

/**
* Prepare statement and store it
*
* @param  string $stmt the SQL statement to prepare
* @throws Exception if error preparing statement
*/
protected function prepare($stmt)
{
	$this->sth = $this->dbh->prepare($stmt);
	if ($this->sth === FALSE)
	{
		throw new RuntimeException('Error preparing statement: ' . $stmt);
	}
}

/**
* Execute prepared statement with some values
*
* @param  array $params array of values to bind to statement
* @return boolean TRUE if successful, or FALSE on failure
* @throws Exception if error executing statement
* @uses   Util_DB_Adapter_MYSQLI::buildBindString()
*/
protected function execute(array $params = array())
{
	// Free previous result, if any
	$this->freeResult();

	// Bind parameters to prepared statement
	$types = Adapter::buildBindString($params);
	array_unshift($params, $types);
	$result = call_user_func_array(array($this->sth, 'bind_param'), $params);
	if (empty($result))
	{
		return FALSE;
	}

	// Execute statement and return result
	$result = $this->sth->execute();
	if ($result === FALSE)
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
* @return boolean TRUE on success, or FALSE on failure
*/
public function freeResult()
{
	// Free result
	$result = TRUE;
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
* @return integer the number of rows in the result set, or FALSE on failure
*/
public function numRows()
{
	return (empty($this->res)) ? FALSE : $this->res->num_rows;
}

/*=======================================================*/
}