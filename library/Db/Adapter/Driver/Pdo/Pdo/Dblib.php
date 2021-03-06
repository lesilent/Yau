<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_DB
*/

namespace Yau\Db\Adapter\Driver\Pdo\Pdo;

use Yau\Db\Adapter\Driver\Pdo\Pdo;

/**
* Database adapter class for use with PDO DBLIB connection objects
*
* @author   John Yau
* @category Yau
* @package  Yau_DB
* @link     http://www.php.net/manual/en/ref.pdo-dblib.php
*/
class Dblib extends Pdo
{
/*=======================================================*/

/**
* Execute a statement and return the number of affected rows
*
* @param  string  $stmt the SQL statement to execute
* @param  array   $params optional array of values to bind to query
* @return integer the number of rows affected, or FALSE on error
*/
public function exec($stmt, array $params = [])
{
	return (($rowcount = parent::exec($stmt, $params)) < 0
		&& ($sth = $this->dbh->query('SELECT @@ROWCOUNT')))
		? $sth->fetchColumn()
		: $rowcount;
}

/**
* Begin a transaction
*
* @return boolean TRUE on success or FALSE on failure
*/
public function beginTransaction()
{
	return $this->dbh->exec('BEGIN TRANSACTION') && ($this->transaction = true);
}

/**
* Commit a transaction
*
* @return boolean TRUE on success or FALSE on failure
*/
public function commit()
{
	return $this->dbh->exec('COMMIT TRANSACTION') && (($this->transaction = false) || true);
}

/**
* Rollback the current transaction
*
* @return boolean TRUE on success or FALSE on failure
*/
public function rollBack()
{
	return $this->dbh->exec('ROLLBACK TRANSACTION') && (($this->transaction = false) || true);
}

/**
* Return the id of the last inserted row
*
* @return string the id of the last inserted row
* @uses   PDO::lastInsertId()
*/
public function lastInsertId()
{
	return (($sth = $this->dbh->query('SELECT SCOPE_IDENTITY() AS id')) && ($id = $sth->fetchColumn()))
		? $id : parent::lastInsertId();
}

/*=======================================================*/
}
