<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_MDBAC
*/

namespace Yau\MDBAC;

use Yau\MDBAC\Config;
use Yau\MDBAC\Exception\ConnectException;
use Yau\MDBAC\Exception\InvalidArgumentException;

/**
* Class for connecting to multiple databases
*
* MDBAC = Multi DataBase Adapter Connector
*
* <code>
* require_once 'Yau/autoload.php';
*
* use Yau\MDBAC\MDBAC;
* $mdbac = new MDBAC('db.conf.xml');
*
* $dbh = $mdbac->connect('PEAR_DB', 'mydb');
* </code>
*
* @author   John Yau
* @category Yau
* @package  Yau_MDBAC
*/
class MDBAC
{
/*=================================================================*/

/**
* Constants representing the various connection return types
*
* @var string
*/
const ADODB          = 'ADODB';
const DB2            = 'DB2';
const DBX            = 'DBX';
const CLI            = 'CLI';
const CLI_DB2        = 'CLI_DB2';
const CLI_MYSQL      = 'CLI_MYSQL';
const CLI_ODBC       = 'CLI_ODBC';
const IBM            = 'IBM';
const MSSQL          = 'MSSQL';
const MYSQL          = 'MYSQL';
const MYSQLI         = 'MYSQLI';
const ODBC           = 'ODBC';
const PDO            = 'PDO';
const PDO_DBLIB      = 'PDO_DBLIB';
const PDO_IBM        = 'PDO_IBM';
const PDO_MYSQL      = 'PDO_MYSQL';
const PDO_ODBC       = 'PDO_ODBC';
const PDO_SQLSRV     = 'PDO_SQLSRV';
const PEAR           = 'PEAR';
const PEAR_DB        = 'PEAR_DB';
const PEAR_MDB2      = 'PEAR_MDB2';
const PERL_DBI       = 'PERL_DBI';
const PGSQL          = 'PGSQL';
const PYTHON_MYSQL   = 'PGSQL';
const PYTHON_MYSQLDB = 'PYTHON_MYSQLDB';

/**
* Path or xml string of the database connection info
*
* @var string
*/
private $xml;

/**
* The connection information from the last connection attempt
*
* @var array
*/
protected $info;

/**
* The connection configuration object
*
* @var array
*/
protected $config;

/**
* Cache of connections
*
* @var array
*/
protected $conns = [];

/**
* Associative array of options
*
* @var array
*/
protected $options = [];

/**
* Constructor
*
* <options>
* - use_localhost boolean flag to use localhost if host name matches server's hostname
* </code>
*
* @param  string    $xml     path to or string of the database configuration info
* @param  array     $options optional associative array of options
* @throws Exception
*/
public function __construct($xml, $options = [])
{
	$this->xml     = $xml;
	$this->options = $options;
}

/**
* Return the config object for a config file
*
* Example
* <code>
* $config = $mdbac->getConfig();
*
* $result = $config->query('mydb');
* while ($db = $result->fetch())
* {
*     print_r($db);
* }
* </code>
*
* @return object the Yau\MDBAC\Config object of the connection information
*/
public function getConfig()
{
	if (empty($this->config))
	{
		$this->config = new Config($this->xml);
	}
	return $this->config;
}

/**
* Return connection information for connecting to a database
*
* @param  string $database the name of the database to get information for
* @param  array  $options  associative array of connection options
* @return array  array of associative arrays of connection information
*/
public function getDatabaseInfo($database, $options = [])
{
	// Call database-specific method, if available
	$method = 'get' . ucwords($database) . 'Info';
	if (method_exists($this, $method))
	{
		if (($info = $this->$method($options))
			&& is_array($info))
		{
			// If an array is returned, then info was successfully returned
			return $info;
		}
	}

	// Return info as fetched from config
	return $this->getConfig()->fetchAll($database, $options);
}

/**
* Connect to the database
*
* @param string $driver
* @param string $database the name of the database to connect to
* @param array  $options optional array of connection options
*/
public function connect($driver, $database, array $options = [])
{
	// Get database connection information
	$info = $this->getDatabaseInfo($database, $options);

	// Load class for driver
	$driver_dir = (($upos = strpos($driver, '_')) === false)
		? $driver
		: substr($driver, 0, $upos);
	$driver_dir = ucwords(strtolower($driver_dir));
	$class_name = ucwords(str_replace('_', ' ', strtolower($driver)));
	$class_name = 'Yau\\Db\\Connect\\Driver\\' . $driver_dir . '\\' . str_replace(' ', '\\', $class_name);
	if (!\Yau\Yau::classExists($class_name))
	{
		throw new InvalidArgumentException('Unsupported driver ' . $driver);
	}

	// Get the current host
	$host = self::getHostname();

	// Connect to database
	$e = NULL;
	foreach ($info as $params)
	{
		// Store original parameters
		$orig_info = $params;

		// Use localhost if host matches
		if (!empty($this->options['use_localhost'])
			&& !empty($params['host'])
			&& strcmp($params['host'], $host) == 0)
		{
			$params['host'] = 'localhost';
		}

		// Prompt for password if using CLI and password option is true
		if ($driver == 'CLI'
			&& isset($options['password'])
			&& $options['password'] === true)
		{
			$params['password'] = true;
		}

		// Convert parameters for connection
		if (empty($params['driver']))
		{
			$params['driver'] = $driver;
		}

		try
		{
			$dbh = call_user_func([$class_name, 'connect'], $params);

			// Return database object/resource
			if (!empty($dbh))
			{
				$this->info = $orig_info;
				return $dbh;
			}
		}
		catch (\Exception $e)
		{

		}
	}

	// No connection, so no connection info
	$this->info = null;

	// Throw exception if unable to connect; if there was one, then make
	// new one to strip off backtrace that may contain username or password
	$e = (!empty($e) && $e instanceof \Exception)
		? new ConnectException($e->getMessage(), $e->getCode())
		: new ConnectException('Unable to connect to ' . $database);
	throw $e;
}

/**
* This implements the singleton method for connection
*
* Example
* <code>
* // This opens two connections
* $dbh1 = $mdbac->connect('PDO_MYSQL', 'starfleet');
* $dbh2 = $mdbac->connect('PDO_MYSQL', 'starfleet');
*
* // This only opens one connection, where $dbh1 and $dbh2 are equivalent
* $dbh1 = $mdbac->connectOnce('PDO_MYSQL', 'starfleet');
* $dbh2 = $mdbac->connectOnce('PDO_MYSQL', 'starfleet');
* </code>
*
* @param  string $driver   the driver object or resource to return
* @param  string $database the database to connect to
* @param  array  $options  associative array of connection options
* @return mixed
*/
public function connectOnce($driver, $database, $options = [])
{
	// Check for instance
	$conn_key = serialize(func_get_args());
	if (empty($this->conns[$conn_key]))
	{
		$this->conns[$conn_key] = $this->connect($driver, $database, $options);
	}

	// Return connection object/resource
	return $this->conns[$conn_key];
}

/**
* Return the database connection information for the last connection
*
* Example
* <code>
* $dbh = $mdbac->connect('PDO', 'mydb');
* $info = $mdbac->getConnectionInfo();
* print_r($info);
* </code>
*
* @param  string optional
* @return mixed  if a parameter is passed, then the value for that connection
*                parameter. If no value is passed, then the associative array
*                of connection info as returned by the Util_DB_Config object
*                is returned.
*/
public function getConnectionInfo($param = NULL)
{
	// If a parameter is pass, then attempt to return just that one
	if (isset($param))
	{
		return (isset($this->info[$param]))
			? $this->info[$param]
			: NULL;
	}

	return $this->info;
}

/**
* Return the current host name
*
* @return string the name of the current host
*/
protected static function getHostname()
{
	return (isset($_SERVER['HOSTNAME']))
		? $_SERVER['HOSTNAME']
		: php_uname('n');
}

/*=================================================================*/
}
