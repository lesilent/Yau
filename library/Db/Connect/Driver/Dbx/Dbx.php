<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
*/

namespace Yau\Db\Connect\Driver\Dbx;

use Yau\Db\Connect\Driver\DriverInterface;

/**
* Class for connecting to a database and returning the link resource
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
* @link     http://www.php.net/manual/en/ref.dbx.php
*/
class Dbx implements DriverInterface
{
/*=======================================================*/

/**
* Connect to a database using parameters
*
* @param  array    $params     associative array containing the information
*                              for connecting to the database
* @return resource a dbx connection object
* @throws Exception if unable to connect to database successfully
* @see    dbx_connect()
* @link   http://www.php.net/manual/en/function.dbx-connect.php
*/
public static function connect($params)
{
	// Process parameters
	$module = $params['driver'];
	$uc_module = strtoupper($module);
	if (defined('DBX_' . $uc_module))
	{
		$module = constant('DBX_' . $uc_module);
	}
	$host     = $params['host'];
	$database = $params['dbname'];
	$username = $params['username'];
	$password = $params['password'];
	$persistent = (!empty($params['persistent']))
		? DBX_PERSISTENT
		: 0;

	// Connect to database
	$conn = dbx_connect($module, $host, $database, $username, $password, $persistent);

	// Throw exception if there was a connection error
	if ($conn === FALSE)
	{
		throw new ConnectException('Could not connect to ' . $database);
	}

	// Return connection object
	return $conn;
}

/*=======================================================*/
}
