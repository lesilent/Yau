<?php declare(strict_types = 1);

namespace Yau\Db\Connect\Driver\Pear\Pear;

use Yau\Db\Connect\Driver\DriverInterface;
use Yau\Db\Connect\Driver\Pear\Pear;
use RuntimeException;

// Load PEAR DB
if (!class_exists('DB', false)	&& ($path = Pear::getPath()))
{
	require Pear::getPath() . DIRECTORY_SEPARATOR . 'DB.php';
}

/**
 * Class for connecting to a database using PEAR DB
 *
 * @author John Yau
 * @link http://pear.php.net/manual/en/package.database.db.php
 */
class Db extends Pear implements DriverInterface
{
/*=======================================================*/

/**
 * Connect to a database and return PEAR DB object
 *
 * @param array $params associative array containing the information for
 *                      connecting to the database
 * @return object a PEAR DB connection object
 * @throws RuntimeException if unable to connect to database successfully
 * @see DB::parseDSN()
 * @link http://pear.php.net/manual/en/package.database.db.intro-dsn.php
 * @link http://pear.php.net/manual/en/package.database.db.intro-connect.php
 */
public static function connect($params)
{
	$dsn = [
		'phptype'  => $params['driver'],
		'hostspec' => $params['host']
			. (isset($params['port']) ? ':' . $params['port'] : ''),
		'database' => $params['dbname'],
		'username' => $params['username'],
		'password' => $params['password'],

		// Note: no options needs to be an empty array instead of NULL
		// otherwise connections will be persistent
		'options'  => [],
	];
	$options = [];

	// Connect to database
	if (!class_exists('DB'))
	{
		throw new RuntimeException('No PEAR DB loaded');
	}
	$dbh = \DB::connect($dsn, $options);

	// Throw exception if it's an error
	$ERROR_CLASS = 'DB_Error';
	if ($dbh instanceof $ERROR_CLASS || \DB::isError($dbh))
	{
		// Remove DSN that contains un/pw from getUserInfo()
		$message = $dbh->getMessage() . '|' . strstr($dbh->getUserInfo(), ' ** ', true);
		if (isset($params['username']))
		{
			// Add username for debugging
			$message .= $params['username'];
		}
		throw new RuntimeException($message, $dbh->getCode());
	}

	// Return DB object
	return $dbh;
}

/*=======================================================*/
}
