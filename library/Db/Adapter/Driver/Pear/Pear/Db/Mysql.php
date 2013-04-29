<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
*/

namespace Yau\Db\Adapter\Driver\Pear\Pear\Db;

use Yau\Db\Adapter\Driver\Pear\Pear\Db;

/**
* Database adapter for PEAR DB objects with MySQL backend
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
* @link     http://pear.php.net/manual/en/package.database.db.php
*/
class Mysql extends Db
{
/*=======================================================*/

/**
* Return the id of the last row inserted
*
* @return integer the id of the last row inserted, or FALSE
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
* @param  string  $table the name of the table
* @param  array   $params associative array of parameters
* @return integer the number of rows affected, or FALSE on error
* @uses   Util_DB_Adapter_MYSQL::insertIgnoreIntoExec()
*/
public function insertIgnoreInto($table, array $params)
{
	Util::loadClass('Util_DB_Adapter_MYSQL');
	return Util_DB_Adapter_MYSQL::insertIgnoreIntoExec($this, $table, $params);
}

/**
* Wrapper method that allows for easy REPLACE INTO a table
*
* @param  string  $table the name of the table
* @param  array   $params associative array of parameters
* @return integer the number of rows affected, or FALSE on error
* @uses   Util_DB_Adapter_MYSQL::replaceIntoExec()
*/
public function replaceInto($table, array $params)
{
	Util::loadClass('Util_DB_Adapter_MYSQL');
	return Util_DB_Adapter_MYSQL::replaceIntoExec($this, $table, $params);
}

/*=======================================================*/
}
