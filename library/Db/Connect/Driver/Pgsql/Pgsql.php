<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
*/

namespace Yau\Db\Connect\Driver\Pgsql;

use Yau\Db\Connect\Driver\DriverInterface;
use Yau\Db\Connect\Exception\ConnectException;

/**
* Class for connecting to a database and returning the link resource
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
* @link     http://www.php.net/manual/en/ref.pgsql.php
*/
class Pgsql implements DriverInterface
{
/*=======================================================*/

/**
* Connect to a PostgreSQL database using parameters
*
* @param  array   $params associative array containing the information for
*                         connecting to the database
* @param  boolean TRUE to open a persistent connection; default is FALSE
* @return resource a PostgreSQL link identifier resource
* @throws Exception if unable to connect to database successfully
* @see    pg_connect()
* @link   http://www.php.net/manual/en/function.pg-connect.php
* @link   http://www.php.net/manual/en/function.pg-pconnect.php
*/
public static function connect($params)
{
 	// Process parameters
	$pairs = array();
	foreach (array(
		'host'     => 'host',
		'port'     => 'port',
		'dbname'   => 'dbname',
		'username' => 'user',
		'password' => 'password',
		) as $field => $keyword)
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
	if ($conn === FALSE)
	{
		throw new ConnectException(pg_last_error());
	}

	// Return link identifier resource
	return $conn;
}

/*=======================================================*/
}
