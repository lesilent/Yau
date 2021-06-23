<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
*/

namespace Yau\Db\Statement\Driver\Pdo\Pdo;

use Yau\Db\Statement\Driver\Pdo\Pdo;

/**
* Statement object for use with a PDO Mysql connection
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
*/
class Mysql extends Pdo
{
/*=======================================================*/

/**
* Return the number of rows in result
*
* @return integer the number of rows in result, or FALSE on failure
*/
public function numRows()
{
	if (empty($this->sth))
	{
		return FALSE;
	}
	return ($sth = $this->dbh->query('SELECT FOUND_ROWS()'))
		? $sth->fetchOne()
		: FALSE;
}

/*=======================================================*/
}