<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_DB
*/

namespace Yau\Db\Adapter\Driver\Mysql;

use Yau\Db\Adapter\Driver\AbstractDriver;

/**
* Database adapter for mssql resource connections
*
* @author   John Yau
* @category Yau
* @package  Yau_DB
* @link     http://www.php.net/manual/en/ref.mssql.php
*/
class Mssql extends AbstractDriver
{
/*=======================================================*/

/**
* Modify a SQL statement by replacing placeholders with values
*
* Example
* <code>
* $params = array('John', 18);
* $query = 'SELECT lname FROM people WHERE fname = ? AND age > ?';
*
* $query = Mysql::replacePlaceholders($query, $params);
* mysql_query($query);
* </code>
*
* @param  resource $link   the MySQL database connection resource
* @param  string   $stmt   the SQL statement
* @param  array    $params array of input parameters to replace placeholders
*                          with
* @return string   a SQL statement with values in it
* @todo   add additional handling for literal instead of real placeholders
*/
public static function replacePlaceholders($link, $stmt, array $params)
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
			$replacement = '\'' . mysql_real_escape_string($value, $link) . '\'';
		}

		// Replace placeholder with replacement string
		$stmt = substr_replace($stmt, $replacement, $pos, 1);
		$offset = $pos;
	}

	// Return statement
	return $stmt;
}

/**
* Execute a query and return the result
*
* @param  resource $link   the MySQL database connection resource
* @param  string   $stmt   the SQL statement
* @param  array    $params array of input parameters to replace placeholders
*                          with
* @return resource the mysql result resource, or FALSE on error
* @uses   Mysql::replacePlaceholders()
*/
public function executeQuery($link, $query, array $params = array())
{
	// Prepare query
	if (!empty($params))
	{
		$query = self::replacePlaceholders($link, $query, $params);
	}
	return \mysql_query($query, $this->dbh);
}

//-------------------------------------
// Helper methods

/**
* Helper method for MySQL adapters to execute INSERT IGNORE INTO statements
*
* @param  string  $table the name of the table
* @param  array   $params associative array of parameters
* @return integer the number of rows affected, or FALSE on error
* @uses   Util_DB_SQL::buildInsertStatement()
*/
public static function insertIgnoreIntoExec(Util_DB_Adapter $adapter, $table, array $params)
{
	Util::loadClass('Util_DB_SQL');
	$sql = Util_DB_SQL::buildInsertStatement($table, $params);
	$sql = preg_replace('/^INSERT\b/i', 'INSERT IGNORE', $sql);
	return $adapter->exec($sql, $params);
}

/**
* Helper method for MySQL adapters to execute REPLACE INTO statements
*
* @param  string  $table the name of the table
* @param  array   $params associative array of parameters
* @return integer the number of rows affected, or FALSE on error
* @uses   Util_DB_SQL::buildInsertStatement()
*/
public static function replaceIntoExec(Util_DB_Adapter $adapter, $table, array $params)
{
	Util::loadClass('Util_DB_SQL');
	$sql = Util_DB_SQL::buildInsertStatement($table, $params);
	$sql = preg_replace('/^INSERT\b/i', 'REPLACE', $sql);
	return $adapter->exec($sql, $params);
}

//--------------------------------------------

/**
* Execute a statement and return the number of affected rows
*
* @param  string  $stmt a SQL statement to execute
* @param  array   $params optional array of values to bind to placeholders
* @return integer the number of affected rows, or FALSE if error
*/
public function exec($stmt, array $params = array())
{
	$result = self::executeQuery($this->dbh, $stmt, $params);
	return ($result !== FALSE) ? \mysql_affected_rows() : FALSE;
}

/**
* Return the id generated from the last INSERT operation
*
* @return integer the id generated the last INSERT, or FALSE if none
* @uses   mysql_insert_id()
*/
public function lastInsertId()
{
	return \mysql_insert_id($this->dbh);

	// Note: LAST_INSERT_ID() has a bug when inserting into a table with
	// a unique key and the insert fails

	// Fetch last insert id
	$result = \mysql_query('SELECT LAST_INSERT_ID()', $this->dbh);
	if ($row = \mysql_fetch_row($result))
	{
		return $row[0];
	}
	\mysql_free_result($result);

	// Return FALSE
	return FALSE;
}

//--------------------------------------------

/**
* Execute a query and return the value from the first column of the first row
*
* @param  string $query  the SQL query to execute
* @param  array  $params optional array of values to bind to query
* @return mixed  the value from the first column of the first row of the result
* @throws Exception if using a non-SELECT query
*/
public function getOne($query, array $params = array())
{
	// Execute query
	$result = self::executeQuery($this->dbh, $query, $params);
	if ($result === FALSE)
	{
		throw new Exception(\mysql_error($this->dbh), \mysql_errno($this->dbh));
	}
	if ($result === TRUE)
	{
		throw new Exception('Cannot fetch from a non-SELECT query');
	}

	// Fetch row
	$row = \mysql_fetch_row($result);
	\mysql_free_result($result);

	// Return value
	return (empty($row)) ? FALSE : $row[0];
}

//-------------------------------------
// Transaction methods

/**
* Begin a transaction
*
* @return boolean TRUE on success or FALSE on failure
*/
public function beginTransaction()
{
	$result = \mssql_query('BEGIN TRANSACTION', $this->dbh);
	return !empty($result) && ($this->transaction = TRUE);
}

/**
* Commit current transaction
*
* @return boolean TRUE on success or FALSE on failure
*/
public function commit()
{
	$result = \mssql_query('COMMIT TRANSACTION', $this->dbh);
	return !empty($result) && (($this->transaction = FALSE) || TRUE);
}

/**
* Rollback the current transaction
*
* @return boolean TRUE on success or FALSE on failure
*/
public function rollBack()
{
	$result = \mssql_query('ROLLBACK TRANSACTION', $this->dbh);
	return !empty($result) && (($this->transaction = FALSE) || TRUE);
}

//-------------------------------------
// Miscellaneous methods

/**
* Disconnect from database
*
* @return boolean TRUE upon success, or FALSE on failure
*/
public function disconnect()
{
	$result = \mssql_close($this->dbh);
	unset($this->dbh);
	return $result;
}

//-------------------------------------
// Wrapper methods

/**
* Wrapper method that allows for easy INSERT IGNORE INTO a table
*
* @param  string  $table the name of the table
* @param  array   $params associative array of parameters
* @return integer the number of rows affected, or FALSE on error
* @uses   Mysql::insertIgnoreIntoExec()
*/
public function insertIgnoreInto($table, array $params)
{
	return self::insertIgnoreIntoExec($this, $table, $params);
}

/**
* Wrapper method that allows for easy REPLACE INTO a table
*
* @param  string  $table the name of the table
* @param  array   $params associative array of parameters
* @return integer the number of rows affected, or FALSE on error
* @uses   Mysql::replaceIntoExec()
*/
public function replaceInto($table, array $params)
{
	return self::replaceIntoExec($this, $table, $params);
}

/*=======================================================*/
}
