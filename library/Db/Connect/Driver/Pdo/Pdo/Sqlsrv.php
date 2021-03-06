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
* Class for connecting to a Microsoft SQL database using PDO
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
* @see      PDO
* @link     http://www.php.net/manual/en/ref.pdo-sqlsrv.php
*/
class Sqlsrv extends Pdo
{
/*=======================================================*/

/**
* Connect to a Microsoft SQL database using parameters and return a PDO object
*
* @param  array  $params associative array containing the information for
*                        connecting to the database
* @return object a PDO database object
* @throws Exception if unable to connect to database successfully
* @link   http://us1.php.net/manual/en/ref.pdo-sqlsrv.connection.php
*/
public static function connect($params)
{
	// Process parameters
	$pairs = [];
	foreach ([
		'host'    => 'Server',
		'dbname'  => 'Database',
		'timeout' => 'LoginTimeout',
		] as $field => $keyword)
	{
		if (isset($params[$field]))
		{
			$pairs[] = $keyword . '=' . $params[$field];
		}
	}
	$dsn = 'sqlsrv:' . implode(';', $pairs);
	$username = (isset($params['username'])) ? $params['username'] : null;
	$password = (isset($params['password'])) ? $params['password'] : null;
	$driver_options = self::getDriverOptions($params);

	// Connect to database
	$dbh = new \PDO($dsn, $username, $password, $driver_options);
	$dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
	$dbh->setAttribute(\PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE, true);
	$dbh->setAttribute(\PDO::SQLSRV_ATTR_DIRECT_QUERY, true);
	$dbh->setAttribute(\PDO::SQLSRV_ATTR_FORMAT_DECIMALS, true);
	$dbh->setAttribute(\PDO::SQLSRV_ATTR_DECIMAL_PLACES, 2);
	$dbh->exec('SET NOCOUNT ON');

	// Return PDO object
	return $dbh;
}

/*=======================================================*/
}
