<?php declare(strict_types = 1);

namespace Yau\Db\Connect\Driver\Adodb;

use Yau\Db\Connect\Driver\DriverInterface;
use InvalidArgumentException;
use RuntimeException;

/**
 * Class for connecting to a database using ADOdb
 *
 * @author John Yau
 */
class Adodb implements DriverInterface
{
/*=======================================================*/

/**
 * Form a ADOdb DSN string based on parameters
 *
 * @param  array  $params associative array of database connection information
 * @return string the DSN string used by ADOdb to connect
 */
protected static function buildDSN($params)
{
	// Check driver
	if (empty($params['driver']))
	{
		throw new InvalidArgumentException('No driver defined for ADOdb');
	}

	// Form DSN string
	$dsn = $params['driver'] . '://';
	if (isset($params['username']))
	{
		$dsn .= rawurlencode($params['username'])
			. (isset($params['password'])
			? ':' . rawurlencode($params['password']) . '@'
			: '');
	}
	$dsn .= $params['host']
		. (isset($params['database']) ? '/' . rawurlencode($params['database']) : '')
		. (!empty($params['persistent'])  ? '?persistent=1' : '');

	// Return DSN string
	return $dsn;
}

/**
 * Connect to a database using values passed as a DSN array
 *
 * Connection parameters:
 * <code>
 * - driver   string database driver (eg. mysql, pdo_mysql)
 * - hostname string the host for the database
 * - username string the username used to connect to the database
 * - password string the password for the username
 * - database string name of the database
 * - options  string URI query string separated by ampersands of options
 * </code>
 *
 * @param array $params associative array containing the information for
 *                      connecting to the database
 * @return object an ADOdb object
 * @throws RuntimeException if unable to connect to database successfully
 * @see NewADOConnection()
 * @link https://adodb.org
 */
public static function connect($params)
{
	// Load ADOdb class
	if (!function_exists('NewADOConnection'))
	{
		include 'adodb/adodb-exceptions.inc.php';
		include 'adodb/adodb.inc.php';
	}

	// Form DSN string
	$dsn = self::buildDSN($params);

	// Form ADOdb object
	if (!function_exists('NewADOConnection'))
	{
		throw new RuntimeException('Unable to load NewADOConnection');
	}
	$dbh = NewADOConnection($dsn);
	if ($dbh === false)
	{
		throw new RuntimeException('Unable to connect to ' . $params['hostname']);
	}

	// Return ADOdb object
	return $dbh;
}

/*=======================================================*/
}
