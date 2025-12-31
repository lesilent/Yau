<?php declare(strict_types = 1);

namespace Yau\Db\Connect\Driver\Pgsql;

use Yau\Db\Connect\Driver\DriverInterface;
use RuntimeException;

/**
 * Class for connecting to a database and returning the link resource
 *
 * @author John Yau
 * @link http://www.php.net/manual/en/ref.pgsql.php
 */
class Pgsql implements DriverInterface
{
/*=======================================================*/

/**
 * Connect to a PostgreSQL database using parameters
 *
 * @param array $params associative array containing the information for
 *                      connecting to the database
 * @return resource a PostgreSQL link identifier resource
 * @throws RuntimeException if unable to connect to database successfully
 * @see pg_connect()
 * @link http://www.php.net/manual/en/function.pg-connect.php
 * @link http://www.php.net/manual/en/function.pg-pconnect.php
 */
public static function connect($params)
{
 	// Process parameters
	$pairs = [];
	foreach ([
		'host'     => 'host',
		'port'     => 'port',
		'dbname'   => 'dbname',
		'username' => 'user',
		'password' => 'password',
		] as $field => $keyword)
	{
		if (isset($params[$field]))
		{
			$pairs[] = $keyword . '=' . $params[$field];
		}
	}
	$connection_string = implode(' ', $pairs);
	$connect_type = (!empty($params['new_link']))
		? PGSQL_CONNECT_FORCE_NEW
		: 0;

	// Connect to database
	$level = error_reporting(0);
	$conn = (empty($params['persistent']))
		? pg_connect($connection_string, $connect_type)
		: pg_pconnect($connection_string, $connect_type);
	error_reporting($level);

	// Throw exception if there was a connection error
	if ($conn === false)
	{
		throw new RuntimeException(pg_last_error());
	}

	// Return link identifier resource
	return $conn;
}

/*=======================================================*/
}
