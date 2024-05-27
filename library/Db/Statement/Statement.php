<?php declare(strict_types = 1);

namespace Yau\Db\Statement;

use Yau\Db\Adapter\Adapter;
use InvalidArgumentException;

/**
 * Abstract parent class for use with prepared statements and database results
 *
 * Example
 * <code>
 * $sth = Statement::factory($dbh);
 *
 * $params = array('John');
 * $dbh->query('SELECT fname, lname FROM people WHERE fname = ?', $params);
 *
 * while ($row = $sth->fetchRow())
 * {
 *     print_r($row);
 * }
 * </code>
 *
 * Abstract methods:
 * <ul>
 * <li>prepare()
 * <li>execute()
 * <li>fetchRow()
 * <li>fetchAll()
 * <li>freeResult()
 * </ul>
 *
 * Non-abstract methods:
 * <ul>
 * <li>setFetchMode()
 * <li>getFetchMode()
 * <li>fetchColumn()
 * </ul>
 *
 * @author John Yau
 */
abstract class Statement
{
/*=======================================================*/

/**
 * Factory method for returning a statement object
 *
 * @param mixed  $dbh  the database handler or resource
 * @param string $stmt the SQL statement
 * @return object a Statement subclass
 */
public static function factory($dbh, $stmt)
{
	// Get driver
	$driver = Adapter::getDriver($dbh);
	if (empty($driver))
	{
		throw new InvalidArgumentException('Invalid driver');
	}

	// Define class name
	$driver_dir = (($spos = strpos($driver, '_')) === false)
		? $driver
		: substr($driver, 0, $spos);

	// Return instance of subclass
	$class_name = 'Yau\\Db\\Adapter\\Driver\\' . $driver_dir . '\\' . $driver . 'Statement';
	return new $class_name($dbh, $stmt);
}

/*=======================================================*/
}
