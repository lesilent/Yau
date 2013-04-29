<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
*/

namespace Yau\Db\Connect\Driver\Mysql;

use Yau\Db\Connect\Driver\DriverInterface;
use Yau\Db\Connect\Exception\ConnectException;

/**
* Class for connecting to a MySQL database and returning the link resource
*
* @category Yau
* @package  Yau_Db
* @link     http://www.php.net/manual/en/ref.mysql.php
*/
class Mysql implements DriverInterface
{
/*=======================================================*/

/**
* Connect to a database using parameters
*
* @param  array  $params associative array containing the information for
*                        connecting to the database
* @return resource a MySQL link identifier resource
* @throws Exception if unable to connect to database successfully
* @see    mysql_connect()
* @link   http://www.php.net/manual/en/function.mysql-connect.php
* @link   http://www.php.net/manual/en/function.mysql-select-db.php
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
	$username = (isset($params['username']))
		? $params['username']
		: ini_get('mysql.default_user');
	$password = (isset($params['password']))
		? $params['password']
		: ini_get('mysql.default_password');
	$new_link = !empty($params['new_link']);
	$client_flags = (isset($params['client_flags']))
		? $params['client_flags']
		: 0;

	// Connect to database
	$conn = (empty($params['persistent']))
		? mysql_connect($server, $username, $password, $new_link, $client_flags)
		: mysql_pconnect($server, $username, $password, $client_flags);

	// Throw exception if there was a connection error
	if ($conn === FALSE)
	{
		throw new ConnectException(mysql_error(), mysql_errno());
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
