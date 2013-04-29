<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
*/

namespace Yau\Db\Statement\Driver\Pear;

use Yau\Db\Statement\Driver\AbstractDriver;

/**
* Statement object for use with a PEAR DB object
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
*/
class Db extends AbstractDriver
{
/*=======================================================*/

/**
* Return whether a value is a DB_Error object or not
*
* @param  mixed   $value the variable to check
* @return boolean
* @uses   Util_DB_Adapter_PEAR_DB::isError()
*/
protected static function isError($value)
{
	return Util_DB_Adapter_PEAR_DB::isError($value);
}

/**
* Prepare an SQL statement
*
* @param  string  $stmt the SQL statement to prepare
* @return boolean TRUE on success, or FALSE on failure
* @throws Exception if error preparing statement
*/
protected function prepare($stmt)
{
	$sth = $this->dbh->prepare($stmt);
	if (self::isError($sth))
	{
		throw new Exception($this->res->getMessage(), $this->res->getCode());
		return FALSE;
	}
	$this->sth = $sth;
	return TRUE;
}

/**
* Execute the current prepared statement
*
* @param  array   $params array of values to bind to prepared statement
* @return boolean TRUE on success, or FALSE on failure
* @throws Exception if error executing query
*/
public function execute(array $params = array())
{
	// Free previous result, if any
	$this->freeResult();

	// Execute query
	$this->res = $this->dbh->execute($this->sth, $params);

	// Return result
	if (self::isError($this->res))
	{
		throw new Exception($this->res->getMessage(), $this->res->getCode());
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
	if (is_scalar($this->res) && $this->res == DB_OK)
	{
		return FALSE;
	}

	// Else fetch row using fetch mode
	return $this->res->fetchRow(\DB_FETCHMODE_ASSOC);
}

/**
* Fetch a single row from result set as a numeric array
*
* @return mixed a row from the result set, or FALSE if there are no more
*/
public function fetchNumRow()
{
	// Return FALSE if no results
	if (is_scalar($this->res) && $this->res == DB_OK)
	{
		return FALSE;
	}

	// Else fetch row using fetch mode
	return $this->res->fetchRow(\DB_FETCHMODE_ORDERED);
}

/**
* Fetch all rows from result set
*
* @param  integer $fetchmode the mode specifying type of results to return
* @return array   an associative array from the result set, or FALSE if no more
*/
public function fetchAll()
{
	// Return FALSE if no results
	if (is_scalar($this->res) && $this->res == DB_OK)
	{
		return FALSE;
	}

	// Return the results
	return $results;
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
	if (!empty($this->res) && is_object($this->res))
	{
		$result = $this->res->free();
	}
	unset($this->res);

	// Return result
	return $result;
}

/**
* Return the number of rows in result
*
* @return integer the number of rows in result, or FALSE on failure
* @throws Exception if backend does not support this
* @uses   DB_result::numRows()
*/
public function numRows()
{
	// Return FALSE if no results
	if (empty($this->res))
	{
		return FALSE;
	}

	// Get number of rows
	$result = $this->res->numRows();
	if (self::isError($result))
	{
		throw new Exception($result->getMessage(), $result->getCode());
	}

	// Return number of rows
	return $result;
}

/*=======================================================*/
}
