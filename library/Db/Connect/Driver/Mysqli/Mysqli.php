<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
*/

namespace Yau\Db\Connect\Driver\Mysqli;

use Yau\Db\Connect\Driver\DriverInterface;
use Yau\Db\Connect\Exception\ConnectException;

/**
* Class for connecting to a database using mysqli extension
*
* @category Yau
* @package  Yau_Db
* @see      mysqli
* @link     http://www.php.net/manual/en/ref.mysqli.php
*/
class Mysqli implements DriverInterface
{
/*=======================================================*/

/**
* Connect to a database using parameters
*
* @param  array  $params associative array containing the information for
*                        connecting to the database
* @return object a mysqli object
* @throws Exception if unable to connect to database successfully
* @link   http://www.php.net/manual/en/function.mysqli-connect.php
*/
public static function connect($params)
{
	// Process parameters
	$host = (isset($params['host']))
		? $params['host']
		: ini_get('mysqli.default_host');
	$username = (isset($params['username']))
		? $params['username']
		: ini_get('mysqli.default_user');
	$password = (isset($params['password']))
		? $params['password']
		: ini_get('mysqli.default_pw');
	$dbname = (isset($params['dbname']))
		? $params['dbname']
		: '';
	$port = (!empty($params['port']))
		? $params['port']
		: ini_get('mysqli.default_port');
	$socket = (isset($params['socket']))
		? $params['socket']
		: ini_get('mysqli.default_socket');

	// Handle persistent connection request
	if (!empty($params['persistent']))
	{
		$host = 'p:' . $host;
	}

	// Connect to database
	$mysqli = mysqli_connect($host, $username, $passwd, $dbname, $port, $socket);

	// Throw exception if there's an error
	if ($errno = $mysqli->connect_errno)
	{
		throw new ConnectException($mysqli->connect_error, $errno);
	}

	// Return mysqli object
	return $mysqli;
}

/*=======================================================*/
}
