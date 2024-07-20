<?php declare(strict_types = 1);

namespace Yau\Db\Adapter\Driver\Odbc;

use Yau\Db\Adapter\Driver\AbstractDriver;
use ErrorException;

/**
 * Database adapter driver for use with ODBC connection resources
 *
 * @author John Yau
 * @link http://www.php.net/manual/en/ref.uodbc.php
 */
class Odbc extends AbstractDriver
{
/*=======================================================*/

/**
 * Execute a statement and return the number of affected rows
 *
 * @param string $stmt the SQL statement to execute
 * @param array  $params optional array of values to bind to query
 * @return mixed the value from the first column of the first row of the result
 */
public function exec($stmt, array $params = [])
{
	$sth = odbc_prepare($this->dbh, $stmt);
	$res = odbc_execute($sth, $params);
	$result = odbc_num_rows($this->dbh);
	return (empty($result) || $result < 0) ? false : $result;
}

/**
 * Return the id of the last inserted row
 *
 * @return string the id of the last inserted row
 * @throws Exception always
 */
public function lastInsertId()
{
	throw new ErrorException(__METHOD__ . ' is not supported');
}

//-------------------------------------

/**
 * Begin a transaction
 *
 * @return bool true on success or false on failure
 */
public function beginTransaction()
{
	return odbc_autocommit($this->dbh, false) && ($this->transaction = true);
}

/**
 * Commit current transaction
 *
 * @return bool true on success or false on failure
 */
public function commit()
{
	return odbc_commit($this->dbh) && (($this->transaction = false) || true);
}

/**
 * Rollback the current transaction
 *
 * @return bool true on success or false on failure
 */
public function rollBack()
{
	return odbc_rollback($this->dbh) && (($this->transaction = false) || true);
}

//-------------------------------------

/**
 * Disconnect the current connection
 *
 * @return boolean always returns TRUE
 */
public function disconnect()
{
	odbc_close($this->dbh);
	return true;
}

/*=======================================================*/
}
