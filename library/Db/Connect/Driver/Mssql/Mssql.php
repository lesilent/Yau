<?php declare(strict_types = 1);

namespace Yau\Db\Connect\Driver\Mssql;

use Yau\Db\Connect\Driver\DriverInterface;
use RuntimeException;

/**
 * Class for connecting to a MS SQL database and returning the link resource
 *
 * @author  John Yau
 * @link http://www.php.net/manual/en/book.mssql.php
 * @deprecated
 */
class Mssql implements DriverInterface
{
/*=======================================================*/

/**
 * Connect to a database using parameters
 *
 * @param array $params associative array containing the information for
 *                      connecting to the database
 * @return resource a MSSQL link identifier resource
 * @throws RuntimeException if unable to connect to database successfully
 * @see mssql_connect()
 * @link http://www.php.net/manual/en/function.mssql-connect.php
 * @link http://www.php.net/manual/en/function.mssql-pconnect.php
 */
public static function connect($params)
{
	// Process parameters
	$servername = (isset($params['host']))
		? $params['host'] . (!empty($params['port'])
			? (preg_match('/^Win/i', PHP_OS) ? ',' : ':') . $params['port']
			: '')
		: null;
	$username = $params['username'] ?? null;
	$password = $params['password'] ?? null;
	$new_link = !empty($params['new_link']);

	// Connect to database
	$conn = (empty($params['persistent']))
		? mssql_connect($servername, $username, $password, $new_link)
		: mssql_pconnect($servername, $username, $password, $new_link);

	// Throw exception if there was a connection error
	if ($conn === false)
	{
		throw new RuntimeException('Could not connect to database');
	}

	// Select database
	if (isset($params['dbname']))
	{
		mssql_select_db($params['dbname'], $conn);
	}

	// Return link identifier resource
	return $conn;
}

/*=======================================================*/
}
