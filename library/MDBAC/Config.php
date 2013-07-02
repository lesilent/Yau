<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_MDBAC
* @version  2007-12-12
*/

namespace Yau\MDBAC;

use Yau\MDBAC\Result;
use Yau\MDBAC\Exception\InvalidArgumentException;

/**
* Database configuration file class
*
* The API for this object follows that of a database objects. There are
* queries as well as result sets.
*
* Example:
* <code>
* // Register autoloader
* require 'Yau\autoload.php';
*
* // Load class
* use Yau\MDBAC\MDBAC;
*
* $MDBAC = new MDBAC('db.conf.xml');
* $result = $config->query('mydb');
* while ($db = $result->fetch())
* {
*     print_r($db);
* }
* </code>
*
* Possible output from the above example:
* <code>
* Array
* (
*    [name] => mydb
*    [driver] => mysql
*    [dbname] => members
*    [username] => john
*    [password] => fido123
*    [host] = localhost
* )
* </code>
*
* The second parameter can be used to specify a variety of options as to what
* kind of result sets are returned.
*
* Example of using options:
* <code>
* use Yau\MDBAC\Config;
*
* $config = new Config('db.conf.xml');
*
* // To retrieve read-only connection with the username of "john"
* $options = array(
*     'username' => 'john',
*     'access'   => 'read'
* );
* $result = $config->query('mydb', $options);
* </code>
*
* This configuration class can be used in connection with the Yau\Db\Connect
* classes to connect to the database. Since each database object/resource
*
* Example:
* <code>
* use Yau\MDBAC\Config;
*
* $config = new Config('db.conf.xml');
*
* // Get a result set
* $result = $config->query('mydb');
*
* // Attempt to connect to database and return a PDO connection
* while ($db = $result->fetch('PDO_MYSQL'))
* {
*     try { $dbh = Util_DB::connect('PDO_MYSQL, $db); }
*     catch (\Exception $e) { exit($e->getMessage()); }
* }
* </code>
*
* ChangeLog
* <ul>
* <li>20091013: Switched to using array_multisort instead of uasort
* <li>20080303: Fixed bug of not filtering out disabled connections
* <li>20070816: Removed automatic caching to conserve memory since majority of
*               connections involve only a single database.
* </ul>
*
* @author   John Yau
* @category Yau
* @package  Yau_MDBAC
* @link     http://www.w3.org/TR/xpath
*/
class Config
{
/*=======================================================*/

/**
* The current SimpleXML object
*
* @var object
*/
protected $xml;

/**
* Cache of system host information
*
* @var array
*/
protected $systems = array();

//-------------------------------------

/**
* Constructor
*
* System options:
* <pre>
* - name     string the name of the host
* - host     string the host or ip address used to connect
* - disabled string if set, then host is disabled
* </pre>
*
* User options:
* <pre>
* - username string the username used to connect to the database
* - password string the password used to connect to the database
* - default  string define a user as being the default
* - disabled string if set, then username is disabled
* </pre>
*
* Connection options:
* <pre>
* - hostname string  the name of the host as defined in systems area
* - weight   integer the connection weight of the host; default will be 1
* - port     integer optional port number to use to connect to host if other
*                    than default
* - default  string  define a connection as being the default
* - disabled string  if set, then connection to system is temporarily disabled
* </pre>
*
* @param  string $xml     either the path to the config file or a config XML
* @param  array  $options optional associative array of options
* @throws Exception if unable to parse XML
*/
public function __construct($xml, array $options = array())
{
	if (preg_match('/^<\?xml\b/', $xml))
	{
		// If a string, then parse it
		$this->xml = simplexml_load_string($xml);
		if (empty($this->xml))
		{
			throw new InvalidArgumentException('Unable to parse XML');
		}
	}
	elseif (file_exists($xml))
	{
		// If not in cache, then parse
		$this->xml = simplexml_load_file($xml);
		if (empty($this->xml))
		{
			throw new InvalidArgumentException('Unable to parse ' . $xml);
		}
	}
	else
	{
		throw new InvalidArgumentException('Invalid XML passed ' . $xml);
	}

	// Parse XML and store system host information
	foreach ($this->xml->xpath('systems/system') as $node)
	{
		// Form array of host attributes/information
		$system = array();
		foreach ($node->attributes() as $property => $value)
		{
			$system[$property] = (string) $value;
		}

		// Store info by host name
		$name = (string) $node['name'];
		$this->systems[$name] = $system;
	}
}

/**
* Filter callback function to remove undefined or disabled hosts
*
* @param  array   $connection SimpleXML connection element
* @return boolean TRUE if connection passes, or FALSE if not
* @see    array_filter()
*/
protected function connectionFilter($connection)
{
	if (isset($connection['system']))
	{
		$system = (string) $connection['system'];

		// Return TRUE if system exists and not disabled
		if (isset($this->systems[$system])
				&& empty($this->systems[$system]['disabled']))
		{
			return TRUE;
		}
	}

	// Return FALSE
	return FALSE;
}

/**
* Function for sorting an array of users or connections by various weights
*
* The order in terms of ranking is as follows:
* <ol>
* <li>Access type
* <li>Default designation
* <li>Connection weight
* </ol>
*
* @param  array   $arr
* @return boolean
*/
private static function sortWeights(&$arr)
{
	$sortby = array('access'=>array(), 'default'=>array(), 'weight'=>array());
	foreach ($arr as $item)
	{
		$sortby['last'][] = (empty($item['weight'])) ? 0 : 1;
		$sortby['location'][] = (empty($item['location'])) ? 0 : $item['location'];
		$sortby['access'][]  = (empty($item['access']) && $item['weight'] > 0) ? 0 : 1;
		$sortby['default'][] = (empty($item['default'])) ? 0 : 1;
		$sortby['weight'][]  = $item['weight'];
	}
	return array_multisort(
		$sortby['last'], SORT_DESC,
		$sortby['location'], SORT_DESC,
		$sortby['access'], SORT_DESC,
		$sortby['default'], SORT_DESC,
		$sortby['weight'], SORT_DESC,
		$arr);
}

/**
* Return several possible database connection details for a database
*
* Options:
* <pre>
* - access   string specify whether to return "read" or "write" connections;
*                   default is connections that can do both reads and writes
* - username string the name of the username to use if other than default
* - system   string the name of the system to use if other than default
* </pre>
*
* @param  string $database the name of the database information to load
* @param  array  $options  optional associative array of options
* @return array  an array of associative arrays of connection info if it was
*                loaded successfully
*/
public function fetchAll($database, array $options = array())
{
	// Search for the database
	$path = 'databases/database[@name="' . htmlspecialchars($database) . '"]';
	$result = $this->xml->xpath($path);
	if (empty($result))
	{
		// Return empty array if unable to find database
		return array();
	}
	$db = array_shift($result);

	// Store database attributes
	$db_info = array();
	foreach ($db->attributes() as $property => $value)
	{
		$db_info[$property] = (string) $value;
	}

	/*
	* Grab a user to use
	*/

	// Form a list of paths used to search for a user
	$path = NULL;
	if (isset($options['username']))
	{
		// If username passed, then we need at least one
		$need_user = TRUE;

		// Form xpath
		$path = 'users/user[not(@disabled) and @username="'
		      . htmlspecialchars($options['username']) . '"]';
	}
	elseif (isset($db->users))
	{
		// There are users, so we need at least one
		$need_user = TRUE;

		// Form xpath
		$path = 'users/user[not(@disabled)]';
	}
	else
	{
		// If no users, then we don't need one
		$need_user = FALSE;
	}

	// Search paths for user
	$users = (isset($path)) ? $db->xpath($path) : array();

	// If need a user and there is none, then return empty array
	if ($need_user && empty($users))
	{
		return array();
	}

	// Form an array of user weights to aid in sorting
	$arr_weights = array();
	shuffle($users);
	foreach ($users as $i => $user)
	{
		// Set flag that user weights were used
		$weight = (isset($user['weight']))
			? (int) $user['weight']
			: 1;
		$arr_weights[] = array(
			'index'    => $i,
			'username' => (string) $user['username'],
			'default'  => (isset($user['default']) ? 1 : 0),
			'weight'   => (($weight > 0) ? mt_rand(1, $weight) : 0),
		);
	}

	// Sort users
	if (count($arr_weights) > 1)
	{
		self::sortWeights($arr_weights);
	}

	// Pick a user and store its attributes
	$user_info = array();
	if (!empty($users))
	{
		// Use the first one in the list
		$arr = array_shift($arr_weights);
		$user = $users[$arr['index']];

		// Use the first user
		foreach ($user->attributes() as $property => $value)
		{
			$user_info[$property] = (string) $value;
		}
	}

	/*
	* Grab list of connections to use
	*/

	// Form path for database access type
	$access_path = (isset($options['access']) && $options['access'] == 'read')
		? '(@access="' . htmlspecialchars($options['access']) . '" or not(@access))'
		: 'not(@access)';

	// Form a list of paths used to search for a connection
	$path = NULL;
	if (isset($options['system']))
	{
		// Need a connection if a system option was passed
		$need_connection = TRUE;

		// Form xpath
		$path = 'connections/connection[not(@disabled) and @system="'
		      . htmlspecialchars($options['system'])
		      . '" and ' . $access_path
		      . ']';
	}
	elseif (isset($db->connections))
	{
		// Need a connection if there's database connections are available
		$need_connection = TRUE;

		// Form xpath
		$path = 'connections/connection[not(@disabled) and ' . $access_path . ']';
	}
	else
	{
		// No connections associated with database, so we don't need it
		$need_connection = FALSE;
	}

	// Search for connections using the path
	$connections = (isset($path)) ? $db->xpath($path) : array();
	array_filter($connections, array($this, 'connectionFilter'));

	// Return empty array if need a connection, but couldn't find one
	if ($need_connection && empty($connections))
	{
		return array();
	}

	/*
	* Prepare and return result
	*/
	$result = array();

	// If no connections, then have just user and database info
	if (empty($connections))
	{
		$result[] = array_merge($db_info, $user_info);
		return $result;
	}

	// Form an array of connection weights to aid in sorting
	$arr_weights = array();
	shuffle($connections);
	foreach ($connections as $i => $connection)
	{
		$weight = (isset($connection['weight']))
			? (int) $connection['weight']
			: 1;
		$system = (string) $connection['system'];
		$location = (isset($this->systems[$system]['location'])) ? $this->systems[$system]['location'] : '';
		$arr_weights[] = array(
			'index'   => $i,
			'system'  => (string) $connection['system'],
			'default' => (isset($connection['default']) ? 1 : 0),
			'access'  => (isset($connection['access'])  ? 1 : 0),
			'weight'  => (($weight > 0) ? mt_rand(1, $weight) : 0),
			'location' => ((!empty($options['location']) && $options['location'] == $location) ? 2 :
                                (empty($location) ? 1 : 0)),
		);
	}

	// Sort connection weights to determine connection order
	if (count($arr_weights) > 1)
	{
		self::sortWeights($arr_weights);
	}

	// Merge connection and system info with database info
	foreach	($arr_weights as $arr)
	{
		$connection = $connections[$arr['index']];

		// Form array of connection attributes
		$conn_info = array();
		foreach ($connection->attributes() as $property => $value)
		{
			$conn_info[$property] = (string) $value;
		}

		// Add system or host attributes to connection
		// Note: disabled or invalid systems was already filtered above
		$system_info = (isset($conn_info['system']))
			? $this->systems[$conn_info['system']]
			: array();

		// Form all of the info into results
		$result[] = array_merge($system_info, $db_info, $user_info, $conn_info);
	}

	// Return result
	return $result;
}

/**
* Return one possible database connection details for a database
*
* Options:
* <pre>
* - access   string specify whether to return "read" or "write" connections;
*                   default is connections that can do both reads and writes
* - username string the name of the username to use if other than default
* - system   string the name of the system to use if other than default
* </pre>
*
* @param  string $database the name of the database information to load
* @param  array  $options  optional associative array of options
* @return array  an associative arrays of connection info if it was
*                loaded successfully, or NULL if none
* @uses   Yau\MDBAC\Config::fetchAll()
*/
public function fetchOne($database, array $options = array())
{
	$info = $this->fetchAll($database, $options);
	return array_shift($info);
}

/**
* Fetch all connection information for a database and return a result set object
*
* @param  string  $database the name of the database information to load
* @param  array   $options  optional associative array of options
* @return object a Yau\MDBAC\Result result set object
* @uses   Yau\MDBAC\Config::query()
*/
public function query($database, $options = array())
{
	// Fetch a result set
	$result = $this->fetchAll($database, $options);

	// Convert result set into a object
	$result = new Result($result);

	// Return result set object
	return $result;
}

//-------------------------------------
// Validation functions

/**
* Pase a config XML for syntax errors
*
* Throws exceptions for the following:
* - Missing or unreadable file
* - Invalid XML syntax
*
* Trigger errors for the following:
* - Bad values for some tags
*
* @param  mixed   $xml   either the raw XML string or a SimpleXML object
* @param  string  $error optional variable to store the error string
* @return boolean TRUE if the xml passes inspection, or FALSE if it does not
* @see    SimpleXML
* @todo   Add DTD validation
*/
public static function isValidXML($xml, &$error = NULL)
{
	// Check argument that's passed
	if (is_string($xml))
	{
		// Check for proper xml header
		if (substr($xml, 0, 5) != '<?xml')
		{
			$error = 'No XML header found';
			return FALSE;
		}

		// If a string, then parse it
		$xml = simplexml_load_string($xml);
		if (empty($xml))
		{
			$error = 'Unable to parse XML';
			return FALSE;
		}
	}
	elseif (is_object($xml))
	{
		// If object, then check the class
		$object_class = get_class($xml);
		if ($object_class != 'SimpleXMLElement')
		{
			$error = 'Invalid object class of ' . $object_class;
			return FALSE;
		}
	}
	else
	{
		$error = 'Invalid XML type ' . gettype($xml);
		return FALSE;
	}

	// Regular expression for checking hosts
	static $host_regex = '/^(?:localhost|\d+\.\d+\.\d+\.\d+|[a-z0-9\-\.]+\.[a-z]+)$/';

	/*
	* Check system hosts
	*/
	$hosts = array();
	foreach ($xml->xpath('systems/system') as $system)
	{
		// Get system name and host
		$name = (isset($system['name'])) ? (string) $system['name'] : '';
		$host = (isset($system['host'])) ? (string) $system['host'] : '';

		// Check that system name and host are not empty
		if (empty($name))
		{
			$error = 'Empty system label for ' . $host;
			return FALSE;
		}
		if (empty($host))
		{
			$error = 'Empty system host for ' . $name;
			return FALSE;
		}

		// Check system name and host
		if (!preg_match('/^\w+$/', $name))
		{
			$error = 'System name ' . $name . ' not alphanumeric';
			return FALSE;
		}
		if (!preg_match($host_regex, $host))
		{
			$error = 'Bad system host ' . $host;
			return FALSE;
		}

		// Check optional attributes if any
		if (isset($system['access']))
		{
			$access = (string) $system['access'];
			if ($access != 'read' && $access != 'write')
			{
				$error = 'Invalid system access of ' . $access
					. ' for ' . $name;
				return FALSE;
			}
		}
		if (isset($system['port']) && !preg_match('/^\d+$/', $system['port']))
		{
			$port = (string) $system['port'];
			$error = "Invalid port {$port} for {$label}";
			return FALSE;
		}

		// Make sure that names are not duplicated
		if (isset($hosts[$name]))
		{
			$error = 'Duplicate system name ' . $name;
			return FALSE;
		}
		$hosts[$name] = TRUE;
	}

	/*
	* Check databases
	*/
	$db_names = array();
	foreach ($xml->xpath('databases/database') as $database)
	{
		// Check database name
		if (empty($database['name']))
		{
			$error = 'A database without a name found';
			return FALSE;
		}
		$name = (string) $database['name'];
		if (!preg_match('/^\w+$/', $name))
		{
			$error = "Database name {$name} not alphanumeric";
			return FALSE;
		}

		// Check that database name are not duplicated
		if (isset($db_names[$name]))
		{
			$error = "Duplicate database name {$name}";
			return FALSE;
		}
		$db_names[$name] = TRUE;

		/*
		* Check each user set
		*/
		$defaults = 0;
		$user_names = array();
		foreach ($database->xpath('users/user') as $user)
		{
			// Check each user
			if (empty($user['username']))
			{
				$error = "No username defined for a {$name} user";
				return FALSE;
			}
			$username = (string) $user['username'];

			// Make sure that usernames are not duplicated for each database
			if (isset($user_names[$username]))
			{
				$error = "Duplicate username for {$name}";
				return FALSE;
			}
			$user_names[$username] = TRUE;

			// Make sure there's only one default user
			if (!empty($user['default']))
			{
				if (++$defaults > 1)
				{
					$error = "More than one username default for {$name}";
					return FALSE;
				}
			}
		}

		/*
		* Check each connection set
		*/
		$defaults = 0;
		$system_names = array();
		foreach ($database->xpath('connections/connection') as $connection)
		{
			// Check that connection is to a valid system
			if (empty($connection['system']))
			{
				$error = "A connection for {$name} has no system";
				return FALSE;
			}
			$system = (string) $connection['system'];
			if (!isset($hosts[$system]))
			{
				$error = "System {$system} for {$name} not defined";
				return FALSE;
			}

			// Check that systems are not duplicated
			if (isset($system_names[$system]))
			{
				$error = "System {$system} used more than once for {$name}";
				return FALSE;
			}
			$system_names[$system] = TRUE;

			// Check weight
			if (isset($connection['weight']))
			{
				$weight = (string) $connection['weight'];
				if (!preg_match('/^\d+%?$/', $weight))
				{
					$error = 'Invalid connection weight '
						. $weight . ' for system '
						. $system;
				}
			}


			// Make sure that there's only one default connection
			if (!empty($connection['default']))
			{
				if (++$defaults > 1)
				{
					$error = "More than one connection default for {$name}";
					return FALSE;
				}
			}
		}
	}

	// Return TRUE if XML passes all checks
	return TRUE;
}

/**
* Parse a config file and check for syntax errors
*
* Example
* <code>
* $filename = '/home/conf/mydb.conf.xml';
* if (Config::isValidFile($filename))
* {
*     $conf = new Yau\MDBAC\Config($filename);
*     $db = $conf->fetch('mydb');
* }
* else
* {
*     throw new Exception("Invalid config file {$filename}");
* }
* </code>
*
* @param  string  $filename the path to the XML config file
* @param  string  $error    optional variable to store the error string
* @return boolean TRUE if the file passes inspection, or FALSE if it does not
*/
public static function isValidFile($filename, &$error = NULL)
{
	// Check file
	if (!file_exists($filename))
	{
		$error = "Config file {$filename} not found";
		return FALSE;
	}
	if (!is_readable($filename))
	{
		$error = "Unable to read config file {$filename}";
		return FALSE;
	}
	if (filesize($filename) < 10)
	{
		$error = "Config file {$filename} is too small";
		return FALSE;
	}

	// Load file
	$xml = file_get_contents($filename);

	// Check the xml
	return self::isValidXML($xml, $error);
}

/*=======================================================*/
}

