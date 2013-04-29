<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
*/

namespace Yau\Db\Connect\Driver\Pdo\Pdo;

use Yau\Db\Connect\Driver\DriverInterface;
use Yau\Db\Connect\Driver\Pdo\Pdo;


/**
* Class for connecting to an IBM database using PDO
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
* @see      PDO
* @link     http://www.php.net/manual/en/ref.pdo-ibm.php
*/
class Ibm extends Pdo implements DriverInterface
{
/*=======================================================*/

/**
* Connect to a IBM database using parameters and return a PDO object
*
* @param  array  $params associative array containing the information for
*                        connecting to the database
* @return object a PDO database object
* @throws Exception if unable to connect to database successfully
* @link   http://www.php.net/manual/en/ref.pdo-ibm.connection.php
* @todo   add support for uncataloged connections
*/
public static function connect($params)
{
	// Process parameters
	$dsn = 'ibm:' . $params['dbname'];
	$username = (isset($params['username'])) ? $params['username'] : NULL;
	$password = (isset($params['password'])) ? $params['password'] : NULL;
	$driver_options = self::getDriverOptions($params);

	// Connect to database
	$dbh = new \PDO($dsn, $username, $password, $driver_options);
	$dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

	// Return PDO object
	return $dbh;
}

/*=======================================================*/
}
