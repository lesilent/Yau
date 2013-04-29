<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
*/

namespace Yau\Db\Statement\Driver;

use Yau\Db\Statement\Exception\BadMethodCallException;

/**
* Abstract parent class for use with prepared statements and database results
*
* Example
* <code>
* $sth = Statement::factory($dbh, 'SELECT fname, lname FROM people WHERE fname = ?');
*
* $params = array('John');
* $sth->execute($params);
*
* while ($row = $sth->fetchRow())
* {
*     print_r($row);
* }
* </code>
*
* Abstract methods:
* <ul>
* <li>prepare()
* <li>execute()
* <li>fetchRow()
* <li>fetchAll()
* <li>freeResult()
* </ul>
*
* Non-abstract methods:
* <ul>
* <li>setFetchMode()
* <li>getFetchMode()
* <li>fetchColumn()
* </ul>
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
*/
abstract class AbstractDriver implements \Countable
{
/*=======================================================*/

/**
* The database object or resource
*
* @var mixed
*/
protected $dbh;

/**
* The prepared statement resource or object
*
* @var mixed
*/
protected $sth;

/**
* The result resource or object
*
* @var mixed
*/
protected $res;

/**
* Constructor
*
* @param mixed  $dbh  the database object or resource
* @param string $stmt the SQL statement to prepare
*/
public function __construct($dbh, $stmt)
{
	$this->dbh = $dbh;
	$this->prepare($stmt);
}

/**
* Destructor
*/
public function __destruct()
{
	$this->freeResult();
}

//-------------------------------------
// Prepare and execute methods

/**
* Prepare an SQL statement
*
* @param  string $stmt the SQL statement to prepare
* @throws Exception if unable to prepare statement
*/
abstract protected function prepare($stmt);

/**
* Execute the current prepared statement
*
* @param  array $params array of values to bind to prepared statement
* @throws Exception if error executing statement
*/
abstract public function execute(array $params = array());

//-------------------------------------
// Fetch methods

/**
* Fetch a single row from result set as an associative array
*
* Example
* <code>
* while ($row = $res->fetchAssocRow())
* {
*     print_r($row);
* }
* </code>
*
* @return mixed   a row from the result set, or FALSE if there are no more
*/
abstract public function fetchAssocRow();

/**
* Fetch a single row from result set as a numeric array
*
* Example
* <code>
* while ($row = $res->fetchNumRow())
* {
*     print_r($row);
* }
* </code>
*
* @return mixed   a row from the result set, or FALSE if there are no more
*/
abstract public function fetchNumRow();

/**
* Fetch the first column from a single row from result set
*
* @return mixed value from first column in fetched row, or FALSE if none
* @uses   AbstractDriver::fetchNumOne()
*/
public function fetchOne()
{
	return ($row = $this->fetchNumRow()) ? array_shift($row): FALSE;
}

/**
* Alias for fetchAssocRow method
*
* @return mixed a row from the result set, or FALSE if there are no more
* @uses   AbstractDriver::fetchAssocRow()
*/
public function fetchRow()
{
	return $this->fetchAssocRow();
}

/**
* Alias for fetchRow method
*
* @return mixed
* @uses   AbstractDriver::fetchRow()
*/
public function fetch()
{
	return $this->fetchRow();
}

/**
* Fetch all results as an array of associative arrays
*
* Note: this method can be overridden as necessary
*
* @return array
*/
public function fetchAssocAll()
{
	$results = array();
	while ($row = $this->fetchAssocRow())
	{
		$results[] = $row;
	}
	return $results;
}

/*
* Fetch all results as an array of numeric arrays
*
* Note: this method can be overridden as necessary
*
* @return array
*/
public function fetchNumAll()
{
	$results = array();
	while ($row = $this->fetchNumRow())
	{
		$results[] = $row;
	}
	return $results;
}

/**
* Alias for fetchAssocAll method
*
* @return array
*/
public function fetchAll()
{
	return $this->fetchAssocAll();
}

//-------------------------------------

/**
* Free and release the current result set
*
* @return boolean TRUE upon success, or FALSE on failure
*/
abstract public function freeResult();

/**
* Return the number of rows in result set
*
* Note: this needs to be overridden by subclasses
*
* @throws BadMethodCallException always
*/
public function numRows()
{
	throw new BadMethodCallException('This is not supported for this driver');
}

//-------------------------------------
// Countable interface function

/**
* Returns the count of rows in result set
*
* @return integer the number of rows in result set, or FALSE on failure
* @uses   AbstractDriver::numRows()
*/
public function count()
{
	return $this->numRows();
}

/*=======================================================*/
}

