<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
*/

namespace Yau\Db\Adapter\Driver\Sqlite;

use Yau\Db\Adapter\Driver\AbstractDriver;

/**
* Database adapter driver for use with SQLite object
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
* @link     http://www.php.net/manual/en/ref.sqlite.php
*/
class Sqlite extends AbstractDriver
{
/*=======================================================*/

/**
* Modify a SQL statement by replacing placeholders with values
*
* @param  string $stmt   the SQL statement
* @param  array  $params array of input parameters to replace placeholders
*                        with
* @return string
*/
public static function replacePlaceholders($stmt, array $params)
{
	// Variable to store where place placeholder replacement was
	$offset = 0;

	// Replace each of the placeholders
	while ($pos = strpos($stmt, '?', $offset))
	{
		// Figure out the replacement string for statement
		$value = array_shift($params);
		if (is_int($value) || is_float($value))
		{
			$replacement = $value;
		}
		elseif (is_null($value))
		{
			$replacement = 'NULL';
		}
		else
		{
			$replacement = '\'' . sqlite_escape_string($value) . '\'';
		}

		// Replace placeholder with replacement string
		$stmt = substr_replace($stmt, $replacement, $pos, 1);
		$offset = $pos;
	}

	// Return statement
	return $stmt;
}

/**
* Execute a result-less statement
*
* @param  string  $stmt the SQL statement to execute
* @param  array   $params optional array of values to bind to query
* @return integer the number of changed rows, or FALSE if error
*/
public function exec($stmt, array $params = array())
{
	$stmt = self::replacePlaceholders($stmt, $params);

	// Execute statement
	$result = (is_resource($this->dbh))
		? sql_exec($this->dbh, $stmt)
		: $this->dbh->queryExec($stmt);
	if (empty($result))
	{
		return $result;
	}

	// Return number of changed rows
	return (is_resource($this->dbh))
		? sqlite_changes($this->dbh)
		: $this->dbh->changes();
}

/**
* Return the id of the last inserted row
*
* @return string the id of the last inserted row
*/
public function lastInsertId()
{
	return (is_resource($this->dbh))
		? sqlite_last_insert_rowid($this->dbh)
		: $this->dbh->lastInsertRowid();
}

//-------------------------------------

/**
* Begin a transaction
*
* @return boolean TRUE on success or FALSE on failure
*/
public function beginTransaction()
{
	$stmt = 'BEGIN TRANSACTION';
	return ((is_resource($this->dbh))
		? sql_exec($this->dbh, $stmt)
		: $this->dbh->queryExec($stmt)) && ($this->transaction = TRUE);
}

/**
* Commit current transaction
*
* @return boolean TRUE on success or FALSE on failure
*/
public function commit()
{
	$stmt = 'COMMIT TRANSACTION';
	return ((is_resource($this->dbh))
		? sql_exec($this->dbh, $stmt)
		: $this->dbh->queryExec($stmt)) && (($this->transaction = FALSE) || TRUE);
}

/**
* Rollback the current transaction
*
* @return boolean TRUE on success or FALSE on failure
*/
public function rollBack()
{
	$stmt = 'ROLLBACK TRANSACTION';
	return ((is_resource($this->dbh))
		? sql_exec($this->dbh, $stmt)
		: $this->dbh->queryExec($stmt)) && (($this->transaction = FALSE) || TRUE);
}

//-------------------------------------

/**
* Disconnect the current connection
*
* @return boolean always returns TRUE
*/
public function disconnect()
{
	if (is_resource($this->dbh))
	{
		sqlite_close($this->dbh);
	}
	unset($this->dbh);
	return TRUE;
}

/*=======================================================*/
}
