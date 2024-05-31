<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
*/

namespace Yau\Db\Statement\Driver\Mssql;

use Yau\Db\Statement\Driver\AbstractDriver;

/**
* Statement class for use with mssql resources
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
*/
class Mssql extends AbstractDriver
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
*/
protected function prepare($stmt)
{
	$this->stmt = $stmt;
}

/**
* Execute prepared statement with some values
*
* @param  array $params array of values to bind to statement
* @throws Exception if error executing query
* @uses   Util_DB_Adapter_MYSQL::replacePlaceholders()
*/
public function execute(array $params = array())
{
	// Free previous result, if any
	$this->freeResult();

	// Prepare statement
	$stmt = MYSQL::replacePlaceholders($this->dbh, $this->stmt, $params);

	// Execute and store result
	$this->res = mssql_query($stmt, $this->dbh);

	// Return whether query was successful or not
	if ($this->res === FALSE)
	{
		throw new \Exception(mssql_get_last_message());
		return FALSE;
	}
	return TRUE;
}

/**
* Fetch a single row from result set as an associative array
*
* @return mixed a row from the result set, or FALSE if there are no more
*/
public function fetchAssocRow()
{
	// Return FALSE if no results
	if (!is_resource($this->res))
	{
		return FALSE;
	}

	// Check if there are any results
	if (empty($this->res))
	{
		throw new \Exception('No results to fetch from');
	}

	// Fetch and return the row
	return \mssql_fetch_assoc($this->res);
}

/**
* Fetch a single row from result set as an numeric array
*
* @return mixed a row from the result set, or FALSE if there are no more
*/
public function fetchNumRow()
{
	// Return FALSE if no results
	if (!is_resource($this->res))
	{
		return FALSE;
	}

	// Check if there are any results
	if (empty($this->res))
	{
		throw new Exception('No results to fetch from');
	}

	// Fetch and return the row
	return \mysql_fetch_row($this->res);
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
	if (!empty($this->res) && is_resource($this->res))
	{
		$result = mssql_free_result($this->res);
	}
	unset($this->res);

	// Return result
	return $result;
}

/**
* Return the number of rows in result
*
* @return integer the number of rows in result, or FALSE on failure
* @uses   mssql_num_rows()
*/
public function numRows()
{
	return (!empty($this->res) && is_resource($this->res))
		? mssql_num_rows($this->res) : FALSE;
}

/*=======================================================*/
}