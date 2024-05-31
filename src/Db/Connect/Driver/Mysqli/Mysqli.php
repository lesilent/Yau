<?php declare(strict_types = 1);

namespace Yau\Db\Connect\Driver\Mysqli;

use Yau\Db\Connect\Driver\DriverInterface;
use RuntimeException;

/**
 * Class for connecting to a database using mysqli extension
 *
 * @author John Yau
 * @see mysqli
 * @link https://www.php.net/manual/en/book.mysqli.php
 */
class Mysqli implements DriverInterface
{
/*=======================================================*/

/**
 * Connect to a database using parameters
 *
 * @param array $params associative array containing the information for
 *                      connecting to the database
 * @return object a mysqli object
 * @throws RuntimeException if unable to connect to database successfully
 * @link http://www.php.net/manual/en/function.mysqli-connect.php
 */
public static function connect($params)
{
	// Process parameters
	$host = $params['host'] ?? ini_get('mysqli.default_host');
	$username = $params['username'] ?? ini_get('mysqli.default_user');
	$password = $params['password'] ?? ini_get('mysqli.default_pw');
	$dbname = $params['dbname'] ?? '';
	$port = (empty($params['port'])) ? ini_get('mysqli.default_port') : $params['port'];
	$socket = $params['socket'] ?? ini_get('mysqli.default_socket');

	// Handle persistent connection request
	if (!empty($params['persistent']))
	{
		$host = 'p:' . $host;
	}

	// Connect to database
	$mysqli = mysqli_connect($host, $username, $password, $dbname, $port, $socket);

	// Throw exception if there's an error
	if ($errno = $mysqli->connect_errno)
	{
		throw new RuntimeException($mysqli->connect_error, $errno);
	}

	// Return mysqli object
	return $mysqli;
}

/*=======================================================*/
}
