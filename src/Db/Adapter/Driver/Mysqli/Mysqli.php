<?php declare(strict_types = 1);

namespace Yau\Db\Adapter\Driver\Mysqli;

use Yau\Db\Adapter\Driver\AbstractDriver;
use Yau\Db\Sql\Sql;

/**
 * Database adapter for use with mysqli objects
 *
 * @author John Yau
 * @link http://www.php.net/manual/en/ref.mysqli.php
 */
class Mysqli extends AbstractDriver
{
/*=======================================================*/

/**
 * Build a string that specify the types for bind values
 *
 * @param array $params array of input parameters to bind to statement
 * @return string
 * @link http://www.php.net/manual/en/mysqli-stmt.bind-param.php
 */
public static function buildBindString(array $params):string
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
 * @param string $stmt the SQL statement to execute
 * @param array $params optional array of values to bind to placeholders
 * @return int the number of affected rows, or false if error
 */
public function exec($stmt, array $params = [])
{
	// Prepare statement
	$sth = $this->dbh->prepare($stmt);
	if (empty($sth))
	{
		return false;
	}

	// Bind parameters to prepared statement
	$types = self::buildBindString($params);
	array_unshift($params, $types);
	$result = call_user_func_array(array($sth, 'bind_param'), $params);
	if (empty($result))
	{
		return false;
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
	return ($row = $result->fetch_row()) ? reset($row) : false;
}

//-------------------------------------
// Transaction methods

/**
 * Begin a transaction
 *
 * @return bool true on success or false on failure
 * @uses mysqli::autocommit()
 */
public function beginTransaction()
{
	return $this->dbh->autocommit(false) && ($this->transaction = true);
}

/**
 * Commit current transaction
 *
 * @return bool true on success or false on failure
 * @uses mysqli::commit()
 */
public function commit()
{
	return $this->dbh->commit() && (($this->transaction = false) || true);
}

/**
 * Rollback the current transaction
 *
 * @return bool true on success or false on failure
 * @uses mysqli::rollback()
 */
public function rollBack()
{
	return $this->dbh->rollback() && (($this->transaction = false) || true);
}

//-------------------------------------
// Miscellaneous methods

/**
 * Disconnect database connection
 *
 * @return bool true on success, or false on failure
 * @uses mysqli::close()
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
 * @param string $table the name of the table
 * @param array $params associative array of parameters
 * @return int the number of rows affected, or false on error
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
 * @param string $table the name of the table
 * @param array $params associative array of parameters
 * @return int the number of rows affected, or false on error
 */
public function replaceInto($table, array $params)
{
	$sql = Sql::buildInsertStatement($table, $params);
	$sql = preg_replace('/^INSERT\b/i', 'REPLACE', $sql);
	return $this->exec($sql, $params);
}

/*=======================================================*/
}
