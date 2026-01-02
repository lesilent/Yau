<?php declare(strict_types = 1);

namespace Yau\Db\Statement\Driver\Pdo\Pdo;

use Yau\Db\Statement\Driver\Pdo\Pdo;

/**
 * Statement object for use with a PDO Mysql connection
 *
 * @author John Yau
 */
class Mysql extends Pdo
{
/*=======================================================*/

/**
 * Return the number of rows in result
 *
 * @return int|false the number of rows in result, or FALSE on failure
 */
public function numRows()
{
	if (empty($this->sth))
	{
		return false;
	}
	return ($sth = $this->dbh->query('SELECT FOUND_ROWS()'))
		? $sth->fetchOne()
		: false;
}

/*=======================================================*/
}