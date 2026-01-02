<?php declare(strict_types = 1);

namespace Yau\Db\Adapter\Driver\Pear\Pear\Db;

use Yau\Db\Adapter\Driver\Pear\Pear\Db;
use Yau\Db\Sql\Sql;
use Exception;

/**
 * Database adapter for PEAR DB objects with MySQL backend
 *
 * @author John Yau
 * @link http://pear.php.net/manual/en/package.database.db.php
 */
class Mysql extends Db
{
/*=======================================================*/

/**
 * Return the id of the last row inserted
 *
 * @return string|false the id of the last row inserted, or false
 */
public function lastInsertId()
{
	$id = $this->dbh->getOne('SELECT LAST_INSERT_ID()');
	if (self::isError($id))
	{
		throw new Exception($id->getMessage(), $id->getCode());
	}
	return $id;
}

//-------------------------------------
// Wrapper methods

/**
 * Wrapper method that allows for easy INSERT IGNORE INTO a table
 *
 * @param string $table the name of the table
 * @param array $params associative array of parameters
 * @return int the number of rows affected, or FALSE on error
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
 * @return int the number of rows affected, or FALSE on error
 */
public function replaceInto($table, array $params)
{
	$sql = Sql::buildInsertStatement($table, $params);
	$sql = preg_replace('/^INSERT\b/i', 'REPLACE', $sql);
	return $this->exec($sql, $params);
}

/*=======================================================*/
}
