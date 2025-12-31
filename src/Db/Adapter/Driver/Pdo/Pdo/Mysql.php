<?php declare(strict_types = 1);

namespace Yau\Db\Adapter\Driver\Pdo\Pdo;

use Yau\Db\Adapter\Driver\Pdo\Pdo;
use Yau\Db\Sql\Sql;

/**
 * Database adapter class for use with PDO MySQL connection objects
 *
 * @author John Yau
 * @link http://www.php.net/manual/en/ref.pdo-mysql.php
 */
class Mysql extends Pdo
{
/*=======================================================*/

/**
 * Return the id of the last inserted row
 *
 * @return string the id of the last inserted row
 */
public function lastInsertId()
{
	$sth = $this->dbh->query('SELECT LAST_INSERT_ID()');
	$row = $sth->fetchAll(\PDO::FETCH_COLUMN);
	return (isset($row[0])) ? $row[0] : false;
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
