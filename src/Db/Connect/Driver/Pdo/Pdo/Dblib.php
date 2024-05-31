<?php declare(strict_types = 1);

namespace Yau\Db\Connect\Driver\Pdo\Pdo;

use Yau\Db\Connect\Driver\Pdo\Pdo;

/**
* Class for connecting to a Microsoft SQL/Sybase database using PDO
*
* @author John Yau
* @see PDO
* @link http://www.php.net/manual/en/ref.pdo-dblib.php
*/
class Dblib extends Pdo
{
/*=======================================================*/

/**
 * Connect to a Microsoft SQL/Sybase database using parameters and return a PDO object
 *
 * @param array $params associative array containing the information for
 *                      connecting to the database
 * @return object a PDO database object
 * @link http://us1.php.net/manual/en/ref.pdo-dblib.connection.php
 */
public static function connect($params)
{
	// Process parameters
	$pairs = [];
	foreach ([
		'version' => 'version',
		'charset' => 'charset',
		'host'    => 'host',
		'dbname'  => 'dbname',
		] as $field => $name)
	{
		if (isset($params[$field]))
		{
			$pairs[] = $name . '=' . $params[$field];
		}
	}
	$dsn = 'dblib:' . implode(';', $pairs);
	$username = $params['username'] ?? null;
	$password = $params['password'] ?? null;
	$driver_options = self::getDriverOptions($params);

	// Connect to database
	$dbh = new \PDO($dsn, $username, $password, $driver_options);
	$dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
	$dbh->exec('SET NOCOUNT ON');

	// Return PDO object
	return $dbh;
}

/*=======================================================*/
}
