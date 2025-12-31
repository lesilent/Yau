<?php declare(strict_types = 1);

namespace Yau\Db\Adapter\Driver\Pdo\Pdo;

use Yau\Db\Adapter\Driver\Pdo\Pdo;

/**
 * Database adapter class for use with PDO DBLIB connection objects
 *
 * @author John Yau
 * @link http://www.php.net/manual/en/ref.pdo-sqlsrv.php
 */
class Sqlsrv extends Pdo
{
/*=======================================================*/

/**
 * Execute a statement and return the number of affected rows
 *
 * @param string $stmt the SQL statement to execute
 * @param array $params optional array of values to bind to query
 * @return int the number of rows affected, or false on error
 */
public function exec($stmt, array $params = [])
{
	if (defined('PDO::SQLSRV_ATTR_DIRECT_QUERY')
		&& ($SQLSRV_ATTR_DIRECT_QUERY = constant('PDO::SQLSRV_ATTR_DIRECT_QUERY')))
	{
		// If not direct query, then we need to enable to properly get rowcount
		// https://blogs.iis.net/bswan/how-to-change-database-settings-with-the-pdo-sqlsrv-driver
		$direct_query = $this->dbh->getAttribute($SQLSRV_ATTR_DIRECT_QUERY);
		if (empty($direct_query))
		{
			$this->dbh->setAttribute($SQLSRV_ATTR_DIRECT_QUERY, true);
		}
	}
	try
	{
		if (($rowcount = parent::exec($stmt, $params)) < 0
			&& ($sth = $this->dbh->query('SELECT @@ROWCOUNT')))
		{
			$rowcount = $sth->fetchColumn();
		}
	}
	finally
	{
		// Revert back to previous direct query setting
		if (!empty($SQLSRV_ATTR_DIRECT_QUERY) && empty($direct_query))
		{
			$this->dbh->setAttribute($SQLSRV_ATTR_DIRECT_QUERY, false);
		}
	}
	return $rowcount;
}

/**
 * Begin a transaction
 *
 * @return bool true on success or false on failure
 */
public function beginTransaction()
{
	return $this->dbh->exec('BEGIN TRANSACTION') && ($this->transaction = true);
}

/**
 * Commit a transaction
 *
 * @return bool true on success or false on failure
 */
public function commit()
{
	return $this->dbh->exec('COMMIT TRANSACTION') && (($this->transaction = false) || true);
}

/**
 * Rollback the current transaction
 *
 * @return bool true on success or false on failure
 */
public function rollBack()
{
	return $this->dbh->exec('ROLLBACK TRANSACTION') && (($this->transaction = false) || true);
}

/**
 * Return the id of the last inserted row
 *
 * @return string the id of the last inserted row
 * @uses PDO::lastInsertId()
 */
public function lastInsertId()
{
	return (($sth = $this->dbh->query('SELECT SCOPE_IDENTITY() AS id')) && ($id = $sth->fetchColumn()))
		? $id : parent::lastInsertId();
}

/*=======================================================*/
}
