<?php declare(strict_types = 1);

namespace Yau\Db\Connect\Driver\Pear\Pear;

use Yau\Db\Connect\Driver\DriverInterface;
use Yau\Db\Connect\Driver\Pear\Pear;
use RuntimeException;

// Load MDB2
if (!class_exists('\MDB2', false) && ($path = Pear::getPath()))
{
 	require Pear::getPath() . DIRECTORY_SEPARATOR . 'MDB2.php';
}

/**
 * Class for connecting to a database using PEAR MDB2
 *
 * @author John Yau
 * @see MDB2
 * @link http://pear.php.net/manual/en/package.database.mdb2.php
 */
class Mdb2 extends Pear implements DriverInterface
{
/*=======================================================*/

/**
 * Connect to a database using parameters and return a MDB2 object
 *
 * Connection parameters:
 * <pre>
 * - phptype  string the database backend used in PHP
 * - dbsyntax string database used with regards to SQL syntax
 * - protocol string communication protocol to use
 * - hostspec string host specification
 * - database string database to use on the DBMS server
 * - username string user name for login
 * - password string password for login
 * - options  array  associative array of connection options.
 * </pre>
 *
 * @param array $params associative array containing the information for
 *                      connecting to the database
 * @return object a PEAR MDB2 connection object
 * @throws RuntimeException if unable to connect to database successfully
 * @see MDB2::parseDSN()
 * @see MDB2::connect()
 * @link http://pear.php.net/manual/en/package.database.mdb2.intro-dsn.php
 * @link http://pear.php.net/manual/en/package.database.mdb2.intro-connect.php
 */
public static function connect($params)
{
	// Process parameters
	$dsn = [];
	foreach ([
		'driver'   => 'phptype',
		'username' => 'username',
		'password' => 'password',
		'host'     => 'hostspec',
		'port'     => 'port',
		]as $field => $name)
	{
		if (isset($params[$field]))
		{
			$dsn[$name] = $params[$field];
		}
	}
	$options = [];
	if (!empty($params['persistent']))
	{
		$options['persistent'] = true;
	}

	// Connect to database
	$dbh = \MDB2::connect($dsn, $options);

	// Throw exception if it's an error
	$ERROR_CLASS = 'PEAR_Error';
	if ($dbh instanceof $ERROR_CLASS || \PEAR::isError($dbh))
	{
		throw new RuntimeException($dbh->getMessage(), $dbh->getCode());
	}

	// Return DB object
	return $dbh;
}

/*=======================================================*/
}
