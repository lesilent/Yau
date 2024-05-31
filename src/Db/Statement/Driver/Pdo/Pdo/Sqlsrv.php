<?php declare(strict_types = 1);

namespace Yau\Db\Statement\Driver\Pdo\Pdo;

use Yau\Db\Statement\Driver\Pdo\Pdo;

/**
 * Statement object for use with a PDO SQLSRV connection
 *
 * @author John Yau
 */
class Sqlsrv extends Pdo
{
/*=======================================================*/

/**
 * Flag for whether we've already cleared first row set
 *
 * @var bool
 * @link https://bugs.php.net/bug.php?id=69592
 */
private $next_rowset = false;

/**
* Prepare statement and store it
*
* @param string $stmt the SQL statement to prepare
*/
protected function prepare($stmt)
{
	// Emulate prepares to deal with this issue:
	// https://aoeex.com/phile/working-around-scope-identity-not-working-with-pdo
	//$options = (stripos($stmt, 'INSERT') === false) ? [] : [\PDO::ATTR_EMULATE_PREPARES=>true];
	$options = [];
	$this->sth = $this->dbh->prepare($stmt, $options);
}

/**
* Fetch a single row from result set as an associative array
*
* @return mixed a row from the result set, or false if there are no more
*/
public function fetchAssocRow()
{
	$row = $this->sth->fetch(\PDO::FETCH_ASSOC);
	if ($row === false && !$this->next_rowset && $this->sth->nextRowset())
	{
		$this->next_rowset = true;
		$row = $this->sth->fetch(\PDO::FETCH_ASSOC);
	}
	return $row;
}

/**
* Fetch a single row from result set as a numeric array
*
* @return mixed a row from the result set, or false if there are no more
*/
public function fetchNumRow()
{
	$row = $this->sth->fetch(\PDO::FETCH_NUM);
	if ($row === false && !$this->next_rowset && $this->sth->nextRowset())
	{
		$this->next_rowset = true;
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
		$this->next_rowset = true;
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
		$this->next_rowset = true;
		$rows = $this->seth->fetchAll(\PDO::FETCH_NUM);
	}
	return $rows;
}

/*=======================================================*/
}