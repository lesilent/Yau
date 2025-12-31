<?php declare(strict_types = 1);

namespace Yau\Db\Connect\Driver\Odbc;

use Yau\Db\Connect\Driver\DriverInterface;
use RuntimeException;

/**
 * Class for connecting to a database and returning the link resource
 *
 * @author John Yau
 * @link http://www.php.net/manual/en/ref.uodbc.php
 */
class Odbc implements DriverInterface
{
/*=======================================================*/

/**
 * Connect to an ODBC database using parameters
 *
 * @param array $params associative array containing the information
 *                      for connecting to the database
 * @return resource a ODBC link identifier resource
 * @throws RuntimeException if unable to connect to database successfully
 * @see odbc_connect()
 * @see odbc_pconnect()
 * @link http://www.php.net/manual/en/function.odbc-connect.php
 * @link http://www.php.net/manual/en/function.odbc-pconnect.php
 */
public static function connect($params)
{
	// Process parameters
	$dsn = $params['dbname'] ?? ini_get('odbc.default_db');
	$user = $params['username'] ?? ini_get('odbc.default_user');
	$password = $params['password'] ?? ini_get('odbc.default_pw');
	$cursor_type = (!empty($params['cursor_type']))
		? $params['cursor_type']
		: intval(ini_get('odbc.default_cursortype'));

	// Connect to database
	$conn = (empty($params['persistent']))
		? odbc_connect($dsn, $user, $password, $cursor_type)
		: odbc_pconnect($dsn, $user, $password, $cursor_type);

	// Throw exception if there was a connection error
	if ($conn === false)
	{
		throw new RuntimeException(odbc_errormsg(), (int) odbc_error());
	}

	// Return link identifier resource
	return $conn;
}

/*=======================================================*/
}
