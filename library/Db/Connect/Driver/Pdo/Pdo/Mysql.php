<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
*/

namespace Yau\Db\Connect\Driver\Pdo\Pdo;

use Yau\Db\Connect\Driver\Pdo\Pdo;

/**
* Class for connecting to a MySQL database using PDO
*
* @category Yau
* @package  Yau_Db
* @see      PDO
* @link     http://www.php.net/manual/en/ref.pdo-mysql.php
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
* @param  array  $params associative array containing the information for
*                        connecting to the database
* @return object a PDO database object
* @throws Exception if unable to connect to database successfully
* @link   http://www.php.net/manual/en/ref.pdo-mysql.connection.php
*/
public static function connect($params)
{
	// Array to hold name/value pairs for DSN string
	$values = array();

	// Form DSN string
	$names = array('host', 'port', 'dbname', 'unix_socket');
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
	$username = (isset($params['username'])) ? $params['username'] : NULL;
	$password = (isset($params['password'])) ? $params['password'] : NULL;
	$driver_options = self::getDriverOptions($params);

	// Connect to database
	$dbh = new \PDO($dsn, $username, $password, $driver_options);
	$dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

	// Return PDO object
	return $dbh;
}

/*=======================================================*/
}
