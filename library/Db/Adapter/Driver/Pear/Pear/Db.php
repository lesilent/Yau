<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
*/

namespace Yau\Db\Adapter\Driver\Pear\Pear;

use Yau\Db\Adapter\Driver\AbstractDriver;

/**
* Database adapter for PEAR DB objects
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
* @link     http://pear.php.net/manual/en/package.database.db.php
*/
class Db extends AbstractDriver
{
/*=======================================================*/

/**
* The DB Error class
*
* @var string
*/
private static $DB_ERROR_CLASS = 'DB_Error';

/**
* Return whether a variable is an DB_Error object or not
*
* Note: this method is created to mitigate warnings thrown by DB::isError
* calls
*
* @param  mixed   $value the variable to check
* @return boolean
*/
public static function isError($value)
{
	return (is_object($value) && ($value instanceof self::$DB_ERROR_CLASS || \DB::isError($value)));
}

/**
* Execute a statement and return number of affected rows
*
* @param  string  $stmt   the SQL statement to execute
* @param  array   $params optional array of values to bind to placeholders
* @return integer the number of affected rows, or FALSE if error
*/
public function exec($stmt, array $params = array())
{
	// Execute statement
	$res = $this->dbh->query($stmt, $params);
	return (self::isError($res)) ? FALSE : $this->dbh->affectedRows();
}

//-------------------------------------
// Transaction methods

/**
* Begin a transaction
*
* @return boolean TRUE on success or FALSE on failure
* @uses   DB_common::autoCommit()
*/
public function beginTransaction()
{
	$res = $this->dbh->autoCommit(FALSE);
	return !self::isError($res) && ($this->transaction = TRUE);
}

/**
* Commit current transaction
*
* @return boolean TRUE on success or FALSE on failure
* @uses   DB_common::commit()
*/
public function commit()
{
	$res = $this->dbh->commit();
	return !self::isError($res) && (($this->transaction = FALSE) || TRUE);
}

/**
* Rollback the current transaction
*
* @return boolean TRUE on success or FALSE on failure
* @uses   DB_common::rollback()
*/
public function rollback()
{
	$res = $this->dbh->rollback();
	return !self::isError($res) && (($this->transaction = FALSE) || TRUE);
}

//-------------------------------------
// Miscellaneous methods


/**
* Return the id generated from the last INSERT operation
*
* @throws Exception always
*/
public function lastInsertId()
{
	throw new ErrorException(__FUNCTION__ . ' not supported for current object');
}

/**
* Disconnect database connection
*
* @return boolean TRUE on success, or FALSE on failure
* @uses   DB_common::disconnect()
*/
public function disconnect()
{
	return $this->dbh->disconnect();
}

/*=======================================================*/
}
