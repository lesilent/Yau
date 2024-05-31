<?php declare(strict_types = 1);

namespace Yau\Db\Connect\Driver\Mysql;

use Yau\Db\Connect\Driver\DriverInterface;
use RuntimeException;

/**
 * Class for connecting to a MySQL database and returning the link resource
 *
 * @author John Yai
 * @link http://www.php.net/manual/en/ref.mysql.php
 */
class Mysql implements DriverInterface
{
/*=======================================================*/

/**
* Connect to a database using parameters
*
* @param array $params associative array containing the information for
*                      connecting to the database
* @return resource a MySQL link identifier resource
* @throws RuntimeException if unable to connect to database successfully
* @see mysql_connect()
* @link http://www.php.net/manual/en/function.mysql-connect.php
* @link http://www.php.net/manual/en/function.mysql-select-db.php
*/
public static function connect($params)
{
	// Process parameters
	if (isset($params['host']))
	{
		$server = $params['host'];
		if (!empty($params['port']))
		{
			$server .= ':' . $params['port'];
		}
	}
	elseif (!empty($params['socket']))
	{
		$server = $params['socket'];
	}
	else
	{
		$server = ini_get('mysql.default_host');
	}
	$username = $params['username'] ?? ini_get('mysql.default_user');
	$password = $params['password'] ?? ini_get('mysql.default_password');
	$new_link = !empty($params['new_link']);
	$client_flags = $params['client_flags'] ?? 0;

	// Connect to database
	$level = error_reporting(0);
	$conn = (empty($params['persistent']))
		? mysql_connect($server, $username, $password, $new_link, $client_flags)
		: mysql_pconnect($server, $username, $password, $client_flags);
	error_reporting($level);

	// Throw exception if there was a connection error
	if ($conn === false)
	{
		throw new RuntimeException(mysql_error(), mysql_errno());
	}

	// Select database
	if (isset($params['dbname']))
	{
		mysql_select_db($params['dbname'], $conn);
	}

	// Return link identifier resource
	return $conn;
}

/*=======================================================*/
}
