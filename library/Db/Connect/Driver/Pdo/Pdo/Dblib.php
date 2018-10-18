<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
*/

namespace Yau\Db\Connect\Driver\Pdo\Pdo;

use Yau\Db\Connect\Driver\DriverInterface;
use Yau\Db\Connect\Driver\Pdo\Pdo;

/**
* Class for connecting to a Microsoft SQL/Sybase database using PDO
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
* @see      PDO
* @link     http://www.php.net/manual/en/ref.pdo-dblib.php
*/
class Dblib extends Pdo
{
/*=======================================================*/

/**
* Connect to a Microsoft SQL/Sybase database using parameters and return a PDO object
*
* @param  array  $params associative array containing the information for
*                        connecting to the database
* @return object a PDO database object
* @throws Exception if unable to connect to database successfully
* @link   http://us1.php.net/manual/en/ref.pdo-dblib.connection.php
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
	$username = (isset($params['username'])) ? $params['username'] : null;
	$password = (isset($params['password'])) ? $params['password'] : null;
	$driver_options = self::getDriverOptions($params);

	// Connect to database
	$dbh = new \PDO($dsn, $username, $password, $driver_options);
	$dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

	// Return PDO object
	return $dbh;
}

/*=======================================================*/
}
