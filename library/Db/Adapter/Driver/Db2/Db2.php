<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
*/

namespace Yau\Db\Adapter\Driver\Db2;

use Yau\Db\Adapter\Driver\AbstractDriver;

/**
* Database adapter class for use with DB2 connection objects
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
* @link     http://www.php.net/manual/en/ref.ibm-db2.php
*/
class Db2 extends AbstractDriver
{
/*=======================================================*/

/**
* Execute a statement and return the number of affected rows
*
* @param  string  $stmt   the SQL statement to execute
* @param  array   $params optional array of values to bind to placeholders
* @return integer the number of affected rows, or FALSE if error
*/
public function exec($stmt, array $params = array())
{
	$sth = db2_prepare($this->dbh, $stmt);
	$res = db2_execute($sth, $params);
	return ($res) ? db2_num_rows($this->dbh) : $res;
}

/**
* Return the last insert id
*
* @return integer
*/
public function lastInsertId()
{
	$stmt = 'SELECT IDENTITY_VAL_LOCAL() AS LASTID FROM SYSIBM.SYSDUMMY1';
	$sth = db2_prepare($this->dbh, $stmt);
	db2_execute($sth);
	return (db2_fetch_row($sth)) ? db2_result($sth, 0) : FALSE;
}

//-------------------------------------
// Transaction methods

/**
* Begin a transaction
*
* @return boolean TRUE on success or FALSE on failure
* @uses   db2_autocommit()
*/
public function beginTransaction()
{
	return db2_autocommit($this->dbh, DB2_AUTOCOMMIT_OFF) && ($this->transaction = TRUE);
}

/**
* Commit current transaction
*
* @return boolean TRUE on success or FALSE on failure
* @uses   db2_commit()
*/
public function commit()
{
	return db2_commit($this->dbh) && (($this->transaction = FALSE) || TRUE);
}

/**
* Rollback the current transaction
*
* @return boolean TRUE on success or FALSE on failure
* @uses   db2_rollback()
*/
public function rollBack()
{
	return db2_rollback($this->dbh) && (($this->transaction = FALSE) || TRUE);
}

//-------------------------------------
// Miscellaneous methods

/**
* Disconnect from database
*
* @return boolean TRUE upon success, or FALSE on failure
* @uses   db2_close()
*/
public function disconnect()
{
	return db2_close($this->dbh);
}

/*=======================================================*/
}
