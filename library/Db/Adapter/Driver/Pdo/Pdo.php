<?php declare(strict_types = 1);

namespace Yau\Db\Adapter\Driver\Pdo;

use Yau\Db\Adapter\Driver\AbstractDriver;

/**
 * Database adapter driver for use with PDO connection objects
 *
 * @author John Yau
 * @link http://www.php.net/manual/en/ref.pdo.php
 */
class Pdo extends AbstractDriver
{
/*=======================================================*/

/**
 * Execute a statement and return the number of affected rows
 *
 * @param string $stmt the SQL statement to execute
 * @param array  $params optional array of values to bind to query
 * @return integer the number of rows affected, or FALSE on error
 */
public function exec($stmt, array $params = [])
{
	$sth = $this->dbh->prepare($stmt);
	return ($sth->execute(array_values($params))) ? $sth->rowCount() : false;
}

/**
 * Return the id of the last inserted row
 *
 * @return string|false the id of the last inserted row
 * @uses PDO::lastInsertId()
 */
public function lastInsertId()
{
	return $this->dbh->lastInsertId();
}

//-------------------------------------

/**
 * Begin a transaction
 *
 * @return bool true on success or false on failure
 */
public function beginTransaction()
{
	return $this->dbh->beginTransaction() && ($this->transaction = true);
}

/**
 * Commit current transaction
 *
 * @return bool true on success or false on failure
 */
public function commit()
{
	return $this->dbh->commit() && (($this->transaction = false) || true);
}

/**
 * Rollback the current transaction
 *
 * @return bool true on success or false on failure
 */
public function rollBack()
{
	return $this->dbh->rollBack() && (($this->transaction = false) || true);
}

/**
 * Return whether a transaction is currently active
 *
 * @return bool
 */
public function inTransaction():bool
{
	return (method_exists($this->dbh, 'inTransaction'))
		? $this->dbh->inTransaction()
		: $this->transaction;
}

//-------------------------------------

/**
 * Disconnect the current connection
 *
 * @return bool always returns true
 */
public function disconnect()
{
	unset($this->dbh);
	return true;
}

/*=======================================================*/
}
