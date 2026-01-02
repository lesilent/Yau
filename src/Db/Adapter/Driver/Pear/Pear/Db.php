<?php declare(strict_types=1);

namespace Yau\Db\Adapter\Driver\Pear\Pear;

use Yau\Db\Adapter\Driver\AbstractDriver;
use ErrorException;

/**
 * Database adapter for PEAR DB objects
 *
 * @author John Yau
 * @link http://pear.php.net/manual/en/package.database.db.php
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
 * @param mixed $value the variable to check
 * @return bool
 */
public static function isError($value): bool
{
	return (is_object($value) && ($value instanceof self::$DB_ERROR_CLASS
		|| (class_exists('DB') && \DB::isError($value))));
}

/**
 * Execute a statement and return number of affected rows
 *
 * @param string $stmt the SQL statement to execute
 * @param array $params optional array of values to bind to placeholders
 * @return int the number of affected rows, or FALSE if error
 */
public function exec($stmt, array $params = [])
{
	$res = $this->dbh->query($stmt, $params);
	return (self::isError($res)) ? false : $this->dbh->affectedRows();
}

//-------------------------------------
// Transaction methods

/**
 * Begin a transaction
 *
 * @return bool true on success or false on failure
 * @uses DB_common::autoCommit()
 */
public function beginTransaction()
{
	$res = $this->dbh->autoCommit(false);
	// @phpstan-ignore-next-line
	return !self::isError($res) && ($this->transaction = true);
}

/**
 * Commit current transaction
 *
 * @return bool true on success or false on failure
 * @uses DB_common::commit()
 */
public function commit()
{
	$res = $this->dbh->commit();
	// @phpstan-ignore-next-line
	return !self::isError($res) && (($this->transaction = false) || true);
}

/**
 * Rollback the current transaction
 *
 * @return bool true on success or false on failure
 * @uses DB_common::rollback()
 */
public function rollback()
{
	$res = $this->dbh->rollback();
	// @phpstan-ignore-next-line
	return !self::isError($res) && (($this->transaction = false) || true);
}

//-------------------------------------
// Miscellaneous methods

/**
 * Return the id generated from the last INSERT operation
 *
 * @throws ErrorException always
 */
public function lastInsertId()
{
	throw new ErrorException(__FUNCTION__ . ' not supported for current object');
}

/**
 * Disconnect database connection
 *
 * @return bool true on success, or false on failure
 * @uses DB_common::disconnect()
 */
public function disconnect()
{
	return $this->dbh->disconnect();
}

/*=======================================================*/
}
