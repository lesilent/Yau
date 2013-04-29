<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
*/

namespace Yau\Db\Connect\Driver\Pear\Pear;

use Yau\Db\Connect\Driver\DriverInterface;
use Yau\Db\Connect\Driver\Pear\Pear;
use Yau\Db\Connect\Exception\ConnectException;

// Load PEAR DB
if (!class_exists('\DB', FALSE)	&& ($path = Pear::getPath()))
{
 	require Pear::getPath() . DIRECTORY_SEPARATOR . 'DB.php';
}

/**
* Class for connecting to a database using PEAR DB
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
* @link     http://pear.php.net/manual/en/package.database.db.php
*/
class Db extends Pear implements DriverInterface
{
/*=======================================================*/

/**
* Connect to a database and return PEAR DB object
*
* @param  array  $params associative array containing the information for
*                        connecting to the database
* @return object a PEAR DB connection object
* @throws Exception if unable to connect to database successfully
* @see    DB::parseDSN()
* @link   http://pear.php.net/manual/en/package.database.db.intro-dsn.php
* @link   http://pear.php.net/manual/en/package.database.db.intro-connect.php
*/
public static function connect($params)
{
	$dsn = array(
		'phptype'  => $params['driver'],
		'hostspec' => $params['host']
			. (isset($params['port']) ? ':' . $params['port'] : ''),
		'database' => $params['dbname'],
		'username' => $params['username'],
		'password' => $params['password'],

		// Note: no options needs to be an empty array instead of NULL
		// otherwise connections will be persistent
		'options'  => array(),
	);

	// Connect to database
	$dbh = \DB::connect($params, $options);

	// Throw exception if it's an error
	$ERROR_CLASS = 'DB_Error';
	if ($dbh instanceof $ERROR_CLASS || \DB::isError($dbh))
	{
		// Remove DSN that contains un/pw from getUserInfo()
		$message = $dbh->getMessage() . '|' . strstr($dbh->getUserInfo(), ' ** ', TRUE);
		if (isset($params['username']))
		{
			// Add username for debugging
			$message .= $params['username'];
		}
		throw new ConnectException($message, $dbh->getCode());
	}

	// Return DB object
	return $dbh;
}

/*=======================================================*/
}
