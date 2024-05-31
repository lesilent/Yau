<?php declare(strict_types = 1);

namespace Yau\Db\Connect\Driver\Pdo\Pdo;

use Yau\Db\Connect\Driver\Pdo\Pdo;

/**
 * Class for connecting to a MySQL database using PDO
 *
 * @author John Yau
 * @see PDO
 * @link http://www.php.net/manual/en/ref.pdo-mysql.php
 */
class Mysql extends Pdo
{
/*=======================================================*/

/**
 * Connect to a MySQL database using parameters and return a PDO object
 *
 * Connection parameters:
 * <pre>
 * - host           string the host for the database
 * - dbname         string name of the database
 * - username       string the username used to connect to the database
 * - password       string the password for the username
 * - unix_socket    string the unix socket
 * - driver_options array  either an associative array of driver-specific
 *                         options
 * </pre>
 *
 * @param array $params associative array containing the information for
 *                      connecting to the database
 * @return object a PDO database object
 * @link http://www.php.net/manual/en/ref.pdo-mysql.connection.php
 */
public static function connect($params)
{
	// Array to hold name/value pairs for DSN string
	$values = [];

	// Form DSN string
	$names = ['host', 'port', 'dbname', 'unix_socket'];
	foreach ($names as $n)
	{
		if (isset($params[$n]))
		{
			$values[] = $n . '=' . rawurlencode($params[$n]);
		}
	}

	// Form DSN string
	$dsn = 'mysql:' . implode(';', $values);

	// Username and password
	$username = $params['username'] ?? null;
	$password = $params['password'] ?? null;
	$driver_options = self::getDriverOptions($params);

	// Connect to database
	$dbh = new \PDO($dsn, $username, $password, $driver_options);
	$dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

	// Return PDO object
	return $dbh;
}

/*=======================================================*/
}
