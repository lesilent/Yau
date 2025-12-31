<?php declare(strict_types = 1);

namespace Yau\Db\Adapter\Driver\Db2;

use Yau\Db\Adapter\Driver\AbstractDriver;

/**
 * Database adapter class for use with DB2 connection objects
 *
 * @author John Yau
 * @link http://www.php.net/manual/en/ref.ibm-db2.php
 */
class Db2 extends AbstractDriver
{
/*=======================================================*/

/**
 * Execute a statement and return the number of affected rows
 *
 * @param string $stmt the SQL statement to execute
 * @param array $params optional array of values to bind to placeholders
 * @return int the number of affected rows, or false if error
 */
public function exec($stmt, array $params = [])
{
	$sth = db2_prepare($this->dbh, $stmt);
	$res = db2_execute($sth, $params);
	return ($res) ? db2_num_rows($this->dbh) : $res;
}

/**
 * Return the last insert id
 *
 * @return int
 */
public function lastInsertId()
{
	$sql = 'SELECT IDENTITY_VAL_LOCAL() AS LASTID FROM SYSIBM.SYSDUMMY1';
	$sth = db2_prepare($this->dbh, $sql);
	db2_execute($sth);
	return (db2_fetch_row($sth)) ? db2_result($sth, 0) : false;
}

//-------------------------------------
// Transaction methods

/**
 * Begin a transaction
 *
 * @return bool true on success or false on failure
 * @uses db2_autocommit()
 */
public function beginTransaction()
{
	return db2_autocommit($this->dbh, DB2_AUTOCOMMIT_OFF) && ($this->transaction = true);
}

/**
 * Commit current transaction
 *
 * @return bool true on success or false on failure
 * @uses db2_commit()
 */
public function commit()
{
	return db2_commit($this->dbh) && (($this->transaction = false) || true);
}

/**
 * Rollback the current transaction
 *
 * @return bool true on success or false on failure
 * @uses db2_rollback()
 */
public function rollBack()
{
	return db2_rollback($this->dbh) && (($this->transaction = false) || true);
}

//-------------------------------------
// Miscellaneous methods

/**
 * Disconnect from database
 *
 * @return bool true upon success, or false on failure
 * @uses db2_close()
 */
public function disconnect()
{
	return db2_close($this->dbh);
}

/*=======================================================*/
}
