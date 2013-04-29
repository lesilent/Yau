<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
*/

namespace Yau\Db\Adapter\Driver\Pdo;

use Yau\Db\Adapter\Driver\AbstractDriver;

/**
* Database adapter driver for use with PDO connection objects
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
* @link     http://www.php.net/manual/en/ref.pdo.php
*/
class Pdo extends AbstractDriver
{
/*=======================================================*/

/**
* Execute a statement and return the number of affected rows
*
* @param  string  $stmt the SQL statement to execute
* @param  array   $params optional array of values to bind to query
* @return integer the number of rows affected, or FALSE on error
*/
public function exec($stmt, array $params = array())
{
	$sth = $this->dbh->prepare($stmt);
	return ($sth->execute(array_values($params))) ? $sth->rowCount() : FALSE;
}

/**
* Return the id of the last inserted row
*
* @return string the id of the last inserted row
* @uses   PDO::lastInsertId()
*/
public function lastInsertId()
{
	return $this->dbh->lastInsertId();
}

//-------------------------------------

/**
* Begin a transaction
*
* @return boolean TRUE on success or FALSE on failure
*/
public function beginTransaction()
{
	return $this->dbh->beginTransaction() && ($this->transaction = TRUE);
}

/**
* Commit current transaction
*
* @return boolean TRUE on success or FALSE on failure
*/
public function commit()
{
	return $this->dbh->commit() && (($this->transaction = FALSE) || TRUE);
}

/**
* Rollback the current transaction
*
* @return boolean TRUE on success or FALSE on failure
*/
public function rollBack()
{
	return $this->dbh->rollBack() && (($this->transaction = FALSE) || TRUE);
}

/**
* Return whether a transaction is currently active
*
* @return boolean
*/
public function inTransaction()
{
	return (method_exists($this->dbh, 'inTransaction'))
		? $this->dbh->inTransaction()
		: $this->transaction;
}

//-------------------------------------

/**
* Disconnect the current connection
*
* @return boolean always returns TRUE
*/
public function disconnect()
{
	unset($this->dbh);
	return TRUE;
}

/*=======================================================*/
}
