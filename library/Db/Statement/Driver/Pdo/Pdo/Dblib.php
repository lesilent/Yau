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
* Statement object for use with a PDO DBLIB connection
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
*/
class Dblib extends Pdo
{
/*=======================================================*/

/**
* Flag for whether we've already cleared first row set
*
* @var  boolean
* @link https://bugs.php.net/bug.php?id=69592
*/
private $next_rowset = FALSE;

/**
* Return the number of rows affected by the last SQL statement
*
* @return integer
*/
public function rowCount()
{
	$sth = $this->dbh->query('SELECT @@ROWCOUNT');
	return $sth->fetchColumn();
}

/**
* Fetch a single row from result set as an associative array
*
* @return mixed a row from the result set, or FALSE if there are no more
*/
public function fetchAssocRow()
{
	$row = $this->sth->fetch(\PDO::FETCH_ASSOC);
	if ($row === FALSE && !$this->next_rowset && $this->sth->nextRowset())
	{
		$this->next_rowset = TRUE;
		$row = $this->sth->fetch(\PDO::FETCH_ASSOC);
	}
	return $row;
}

/**
* Fetch a single row from result set as a numeric array
*
* @return mixed a row from the result set, or FALSE if there are no more
*/
public function fetchNumRow()
{
	$row = $this->sth->fetch(\PDO::FETCH_NUM);
	if ($row === FALSE && !$this->next_rowset && $this->sth->nextRowset())
	{
		$this->next_rowset = TRUE;
		$row = $this->sth->fetch(\PDO::FETCH_NUM);
	}
	return $row;
}

/**
* Fetch all results from result set as an array of associative arrays
*
* @return array
*/
public function fetchAssocAll()
{
	$rows = $this->sth->fetchAll(\PDO::FETCH_ASSOC);
	if (empty($rows) && !$this->next_rowset && $this->sth->nextRowset())
	{
		$this->next_rowset = TRUE;
		$rows = $this->sth->fetchAll(\PDO::FETCH_ASSOC);
	}
	return $rows;
}

/**
* Fetch all results from result set as an array of numeric arrays
*
* @return array
*/
public function fetchNumAll()
{
	$rows = $this->sth->fetchAll(\PDO::FETCH_NUM);
	if (empty($rows) && !$this->next_rowset && $this->sth->nextRowset())
	{
		$this->next_rowset = TRUE;
		$rows = $this->seth->fetchAll(\PDO::FETCH_NUM);
	}
	return $rows;
}

/*=======================================================*/
}