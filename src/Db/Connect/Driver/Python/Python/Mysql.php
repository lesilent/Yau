<?php declare(strict_types = 1);

namespace Yau\Db\Connect\Driver\Python\Python;

use Yau\Db\Connect\Driver\DriverInterface;

/**
 * Class for connecting to a database using Python's _mysql module
 *
 * @author John Yau
 * @link http://mysql-python.sourceforge.net/MySQLdb.html
 */
class Mysql implements DriverInterface
{
/*=======================================================*/

/**
 * Return arguments for connecting to MySQL in Python
 *
 * @param array $params associative array containing the information for
 *                      connecting to the database
 * @return array an associative array of arguments for _mysql connect function
 * @link http://mysql-python.sourceforge.net/MySQLdb.html
 */
public static function connect($params)
{
	// Process parameters
	$args = [];
	foreach ([
		'driver'   => 'driver',
		'host'     => 'host',
		'username' => 'user',
		'password' => 'passwd',
		'dbname'   => 'db',
		'port'     => 'port',
		] as $field => $name)
	{
		if (isset($params[$field]))
		{
			$args[$field] = $params[$name];
		}
	}

	// Return arguments;
	return $args;
}

/*=======================================================*/
}
