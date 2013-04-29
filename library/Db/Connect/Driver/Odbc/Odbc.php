<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
*/

namespace Yau\Db\Connect\Driver\Odbc;

use Yau\Db\Connect\Driver\DriverInterface;
use Yau\Db\Connect\Exception\ConnectException;

/**
* Class for connecting to a database and returning the link resource
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
* @link     http://www.php.net/manual/en/ref.uodbc.php
*/
class Odbc implements DriverInterface
{
/*=======================================================*/

/**
* Connect to an ODBC database using parameters
*
* @param  array    $params     associative array containing the information
*                              for connecting to the database
* @param  boolean  $persistent TRUE to open a persistent connection; default is FALSE
* @return resource a ODBC link identifier resource
* @throws Exception if unable to connect to database successfully
* @see    odbc_connect()
* @see    odbc_pconnect()
* @link   http://www.php.net/manual/en/function.odbc-connect.php
* @link   http://www.php.net/manual/en/function.odbc-pconnect.php
*/
public static function connect($params)
{
	// Process parameters
	$dsn = (isset($params['dbname']))
		? $params['dbname']
		: ini_get('odbc.default_db');
	$user = (isset($params['username']))
		? $params['username']
		: ini_get('odbc.default_user');
	$password = (isset($params['password']))
		? $params['password']
		: ini_get('odbc.default_pw');
	$cursor_type = (!empty($params['cursor_type']))
		? $params['cursor_type']
		: intval(ini_get('odbc.default_cursortype'));

	// Connect to database
	$conn = (empty($params['persistent']))
		? odbc_connect($dsn, $user, $password, $cursor_type)
		: odbc_pconnect($dsn, $user, $password, $cursor_type);

	// Throw exception if there was a connection error
	if ($conn === FALSE)
	{
		throw new ConnectException(odbc_error(), odbc_errormsg());
	}

	// Return link identifier resource
	return $conn;
}

/*=======================================================*/
}
