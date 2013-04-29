<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
*/

namespace Yau\Db\Adapter\Driver\Mysqli;

use Yau\Db\Adapter\Driver\AbstractDriver;
use Yau\Db\Sql\Sql;

/**
* Database adapter for use with mysqli objects
*
* @author   John Yau
* @category Yau
* @package  Yau_DB
* @link     http://www.php.net/manual/en/ref.mysqli.php
*/
class Mysqli extends AbstractDriver
{
/*=======================================================*/

/**
* Build a string that specify the types for bind values
*
* @param  array  $params array of input parameters to bind to statement
* @return string
* @link   http://www.php.net/manual/en/mysqli-stmt.bind-param.php
*/
public static function buildBindString(array $params)
{
	// Build types string
	$types = '';
	foreach ($params as $p)
	{
		if (is_int($p))
		{
			$types .= 'i';
		}
		elseif (is_float($p))
		{
			$types .= 'd';
		}
		else
		{
			$types .= (strlen($p) > 255) ? 'b' : 's';
		}
	}

	// Return types string
	return $types;
}

//-------------------------------------

/**
* Execute a statement and return the number of affected rows
*
* @param  string  $stmt   the SQL statement to execute
* @param  array   $params optional array of values to bind to placeholders
* @return integer the number of affected rows, or FALSE if error
*/
public function exec($stmt, array $params = array())
{
	// Prepare statement
	$sth = $this->dbh->prepare($query);
	if (empty($sth))
	{
		return FALSE;
	}

	// Bind parameters to prepared statement
	$types = self::buildBindString($params);
	array_unshift($params, $types);
	$result = call_user_func_array(array($sth, 'bind_param'), $params);
	if (empty($result))
	{
		return FALSE;
	}

	// Execute statement and return number of affected rows
	$sth->execute();
	return $sth->affected_rows;
}

/**
* Return the id of the last inserted row
*
* @return string the id of the last inserted row
*/
public function lastInsertId()
{
	return $this->dbh->insert_id;
	$result = $this->dbh->query('SELECT LAST_INSERT_ID()');
	return ($row = $result->fetch_row()) ? $row[0] : FALSE;
}

//-------------------------------------
// Transaction methods

/**
* Begin a transaction
*
* @return boolean TRUE on success or FALSE on failure
* @uses   mysqli::autocommit()
*/
public function beginTransaction()
{
	return $this->dbh->autocommit(FALSE) && ($this->transaction = TRUE);
}

/**
* Commit current transaction
*
* @return boolean TRUE on success or FALSE on failure
* @uses   mysqli::commit()
*/
public function commit()
{
	return $this->dbh->commit() && (($this->transaction = FALSE) || TRUE);
}

/**
* Rollback the current transaction
*
* @return boolean TRUE on success or FALSE on failure
* @uses   mysqli::rollback()
*/
public function rollBack()
{
	return $this->dbh->rollback() && (($this->transaction = FALSE) || TRUE);
}

//-------------------------------------
// Miscellaneous methods

/**
* Disconnect database connection
*
* @return boolean TRUE on success, or FALSE on failure
* @uses   mysqli::close()
*/
public function disconnect()
{
	return $this->dbh->close();
}

//-------------------------------------
// Wrapper methods

/**
* Wrapper method that allows for easy INSERT IGNORE INTO a table
*
* @param  string  $table the name of the table
* @param  array   $params associative array of parameters
* @return integer the number of rows affected, or FALSE on error
*/
public function insertIgnoreInto($table, array $params)
{
	$sql = Sql::buildInsertStatement($table, $params);
	$sql = preg_replace('/^INSERT\b/i', 'INSERT IGNORE', $sql);
	return $this->exec($sql, $params);
}

/**
* Wrapper method that allows for easy REPLACE INTO a table
*
* @param  string  $table the name of the table
* @param  array   $params associative array of parameters
* @return integer the number of rows affected, or FALSE on error
*/
public function replaceInto($table, array $params)
{
	$sql = Sql::buildInsertStatement($table, $params);
	$sql = preg_replace('/^INSERT\b/i', 'REPLACE', $sql);
	return $this->exec($sql, $params);
}

/*=======================================================*/
}
