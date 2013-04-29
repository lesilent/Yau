<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
*/

namespace Yau\Db\Statement\Driver\Pdo;

use Yau\Db\Statement\Driver\AbstractDriver;

/**
* Statement object for using mysql resources
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
*/
class Pdo extends AbstractDriver
{
/*=======================================================*/

/**
* Prepare statement and store it
*
* @param string $stmt the SQL statement to prepare
*/
protected function prepare($stmt)
{
	$this->sth = $this->dbh->prepare($stmt);
}

/**
* Execute prepared statement with some values
*
* @param  array   $params array of values to bind to statement
* @return boolean TRUE if successfull, or FALSE on failure
*/
public function execute(array $params = array())
{
	// Free previous result, if any
	$this->freeResult();

	// Execute statement
	return $this->sth->execute(array_values($params));
}

/**
* Fetch a single row from result set as an associative array
*
* @return mixed a row from the result set, or FALSE if there are no more
*/
public function fetchAssocRow()
{
	return $this->sth->fetch(\PDO::FETCH_ASSOC);
}

/**
* Fetch a single row from result set as a numeric array
*
* @return mixed a row from the result set, or FALSE if there are no more
*/
public function fetchNumRow()
{
	return $this->sth->fetch(\PDO::FETCH_NUM);
}

/**
* Fetch all results from result set as an array of associative arrays
*
* @return array
*/
public function fetchAssocAll()
{
	return $this->sth->fetchAll(\PDO::FETCH_ASSOC);
}

/**
* Fetch all results from result set as an array of numeric arrays
*
* @return array
*/
public function fetchNumAll()
{
	return $this->sth->fetchAll(\PDO::FETCH_NUM);
}

/**
* Free and release the current result set
*
* Note: the closeCursor() method results in a memory leak; and fetchAll()
* will throw an exception if statement was not a SELECT
*
* @return boolean TRUE on success, or FALSE on failure
*/
public function freeResult()
{
	// Free result
	$result = TRUE;
	if (!empty($this->sth))
	{
/*
		try
		{
			$this->sth->fetchAll();
		}
		catch (Exception $e)
		{
		}
//		$result = $this->sth->closeCursor();
*/
	}

	// Return result
	return $result;
}

/*=======================================================*/
}
