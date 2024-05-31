<?php declare(strict_types = 1);

namespace Yau\MDBAC;

use Yau\MDBAC\Result;
use SimpleXMLElement;
use InvalidArgumentException;
use Exception;

/**
 * Database configuration file class
 *
 * The API for this object follows that of a database objects. There are
 * queries as well as result sets.
 *
 * Example:
 * <code>
 * use Yau\Db\Config;
 *
 * $config = new Config('db.conf.xml');
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
 *     try { $dbh = DB::connect('PDO_MYSQL, $db); }
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
 * @author John Yau
 * @link   http://www.w3.org/TR/xpath
 */
class Config
{
/*=======================================================*/

/**
 * The current JSON data
 *
 * @var array
 */
protected $data;

/**
 * The current SimpleXML object
 *
 * @var object
 */
protected $xml;

/**
 * The data
 *
 * @var array
 */
private $config = [];

/**
 * Cache of system host information
 *
 * @var array
 */
protected $systems = [];

/**
 * Regular expression for checking hosts
 *
 * @var string
 */
private static $HOST_PATTERN = '/^(?:localhost|\d+\.\d+\.\d+\.\d+|[a-z0-9\-\.]+\.[a-z]+)$/';

/**
 * Regular expression for xml
 *
 * @var string
 */
private static $XML_PATTERN = '/^<\?xml\b.+>\s*$/s';

/**
 * Regular expression for json
 *
 * @var string
 */
private static $JSON_PATTERN = '/^\s*{.+}\s*$/s';

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
 * @param mixed $cfg     either the path to the config file or a config XML
 * @param array $options optional associative array of options
 * @throws InvalidArgumentException if unable to parse XML
 */
public function __construct($cfg, array $options = [])
{
	if (empty($cfg))
	{
		throw new InvalidArgumentException('Empty config');
	}
	if (is_object($cfg) && $cfg instanceof SimpleXMLElement)
	{
		$this->xml = $cfg;
		return;
	}
	elseif (is_array($cfg))
	{
		$this->data = $cfg;
		return;
	}
	elseif (!is_string($cfg))
	{
		throw new InvalidArgumentException('Invalid config');
	}

	// If cfg is a filename, then get contents
	$filename = false;
	if (file_exists($cfg))
	{
		$cfg = file_get_contents($filename = $cfg);
		if (empty($cfg))
		{
			throw new InvalidArgumentException("File {$cfg} is blank");
		}
	}

	// Parse config string
	if (preg_match(self::$XML_PATTERN, $cfg))
	{
		// If a string, then parse it
		$use_errors = libxml_use_internal_errors(true);
		$this->xml = simplexml_load_string($cfg);
		libxml_use_internal_errors($use_errors);
		if (empty($this->xml))
		{
			throw ($error = libxml_get_last_error())
				? new InvalidArgumentException($error->message, $error->code)
				: new InvalidArgumentException('Unable to parse XML');
		}

		// Store system host information
		foreach ($this->xml->xpath('systems/system') as $node)
		{
			// Form array of host attributes/information
			$system = [];
			foreach ($node->attributes() as $property => $value)
			{
				$system[$property] = (string) $value;
			}

			// Store info by host name
			$name = (string) $node['name'];
			$this->systems[$name] = $system;
		}
	}
	elseif (preg_match(self::$JSON_PATTERN, $cfg))
	{
		$this->data = json_decode($cfg, true, 32, JSON_THROW_ON_ERROR);
		if (empty($this->data))
		{
			throw new InvalidArgumentException('Unable to parse JSON');
		}

		// Store system host information
		foreach (($this->data['system'] ?? []) as $name => $system)
		{
			$this->systems[$name] = $system;
		}

	//	throw new InvalidArgumentException('JSON currently not supported at this time');
	}
	else
	{
		throw new InvalidArgumentException('Invalid config passed ' . (isset($filename) ? " $filename" : ''));
	}
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
 * @param array $arr
 * @return bool
 */
private function sortWeights(&$arr)
{
	// Return if there isn't enough to sort
	if (count($arr) < 2)
	{
		return false;
	}
	$sortby = ['access'=>[], 'default'=>[], 'weight'=>[]];
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
public function fetchAll($database, array $options = [])
{
	/*
	 * Get database info
	 */
	$db_info = [];
	if (isset($this->xml))
	{
		// Search for the database
		$path = sprintf('databases/database[@name="%s"]', htmlspecialchars($database));
		$result = $this->xml->xpath($path);
		if (empty($result))
		{
			// Return empty array if unable to find database
			return [];
		}
		$db = array_shift($result);

		// Store database attributes
		foreach ($db->attributes() as $property => $value)
		{
			$db_info[$property] = (string) $value;
		}
	}
	else
	{
		// Return empty array if unable to find database
		if (empty($this->data['database'][$database]))
		{
			return [];
		}

		// Store database attributes
		$db = $this->data['database'][$database];
		$db_info += ['name'=>$database] + array_filter($db, fn($value) => is_scalar($value));
	}

	// Form path and callback filter for access type
	if (strcmp($options['access'] ?? '', 'read') == 0)
	{
		$access_path = sprintf('(@access="%s" or not(@access))', htmlspecialchars($options['access']));
		$access_cb = fn($row) => empty($row['access']) || strcmp($row['access'], $options['access']) == 0;
	}
	else
	{
		$access_path = 'not(@access)';
		$access_cb = fn($row) => empty($row['access']);
	}

	/*
	 * Grab a user to use
	 */

	// Form a list of paths used to search for a user
	$need_user = false;
	if (isset($options['username']))
	{
		// If username passed, then we need at least one
		$need_user = true;

		$path = sprintf('users/user[not(@disabled) and @username="%s" and %s]', htmlspecialchars($options['username']), $access_path);
		$callback = fn($row) => empty($row['disabled']) && strcmp($row['username'] ?? '', $options['username']) == 0 && $access_cb($row);
	}
	elseif (isset($this->xml) ? isset($db->users) : isset($db['user']))
	{
		// There are users, so we need at least one
		$need_user = true;

		$path = sprintf('users/user[not(@disabled) and %s]', $access_path);
		$callback = fn($row) => empty($row['disabled']) && $access_cb($row);
	}

	// Search paths for user
	$user_info = [];
	if ($need_user)
	{
		$users = isset($this->xml)
			? $db->xpath($path)
			: array_filter($db['user'] ?? [], $callback);

		// If need a user and there is none, then return empty array
		if (empty($users))
		{
			return [];
		}

		// Form an array of user weights to aid in sorting
		$arr_weights = [];
		shuffle($users);
		foreach ($users as $i => $user)
		{
			// Set flag that user weights were used
			$weight = (int) ($user['weight'] ?? 1);
			$arr_weights[] = [
				'index'    => $i,
				'username' => (string) $user['username'],
				'default'  => (isset($user['default']) ? 1 : 0),
				'access'   => (isset($user['access'])  ? 1 : 0),
				'weight'   => (($weight > 0) ? mt_rand(1, $weight) : 0),
			];
		}

		// Sort users
		$this->sortWeights($arr_weights);

		// Use the first one in the list
		$arr = array_shift($arr_weights);
		$user = $users[$arr['index']];
		if (isset($this->xml))
		{
			foreach ($user->attributes() as $property => $value)
			{
				$user_info[$property] = (string) $value;
			}
		}
		else
		{
			$user_info += $user;
		}
	}


	/*
	* Grab list of connections to use
	*/

	// Form a list of paths used to search for a connection
	$need_connection = false;
	if (isset($options['system']))
	{
		// Need a connection if a system option was passed
		$need_connection = true;

		$path = sprintf('connections/connection[not(@disabled) and @system="%s" and %s]', htmlspecialchars($options['system']), $access_path);
		$callback = fn($row) => empty($row['disabled']) && strcmp($row['system'], $options['system']) == 0 && $access_cb($row);
	}
	elseif (isset($this->xml) ? isset($db->connections) : isset($db['connection']))
	{
		// Need a connection if there's database connections are available
		$need_connection = true;

		$path = sprintf('connections/connection[not(@disabled) and %s]', $access_path);
		$callback = fn($row) => empty($row['disabled']) && $access_cb($row);
	}

	// Search for connections using the path
	$conn_info = [];
	if ($need_connection)
	{
		$connections = (isset($this->xml))
			? $db->xpath($path)
			: array_filter($db['connection'] ?? [], $callback);
		$connections = array_filter($connections, function($row) {
			// Return true if system exists and not disabled
			$system = (string) $row['system'];
			return (isset($this->systems[$system]) && empty($this->systems[$system]['disabled']));
		});

		// Return empty array if couldn't find one
		if (empty($connections))
		{
			return [];
		}

		// Form an array of connection weights to aid in sorting
		$arr_weights = [];
		shuffle($connections);
		foreach ($connections as $i => $connection)
		{
			$weight = (int) ($connection['weight'] ?? 1);
			$system = (string) $connection['system'];
			$location = $this->systems[$system]['location'] ?? '';
			$arr_weights[] = [
				'index'   => $i,
				'system'  => (string) $connection['system'],
				'default' => (isset($connection['default']) ? 1 : 0),
				'access'  => (isset($connection['access'])  ? 1 : 0),
				'weight'  => (($weight > 0) ? mt_rand(1, $weight) : 0),
				'location' => ((!empty($options['location']) && $options['location'] == $location) ? 2 : (empty($location) ? 1 : 0)),
			];
		}

		// Sort connection weights to determine connection order
		$this->sortWeights($arr_weights);
	}

	/*
	* Prepare and return result
	*/
	$result = [];

	// If no connections, then have just user and database info
	if (empty($connections))
	{
		$result[] = array_merge($db_info, $user_info);
	}
	else
	{
		// Merge connection and system info with database info
		foreach	($arr_weights as $arr)
		{
			$connection = $connections[$arr['index']];

			// Form array of connection attributes
			$conn_info = [];
			if (isset($this->xml))
			{
				foreach ($connection->attributes() as $property => $value)
				{
					$conn_info[$property] = (string) $value;
				}
			}
			else
			{
				$conn_info = $connection;
			}

			// Add system or host attributes to connection
			// Note: disabled or invalid systems was already filtered above
			$system_info = (isset($conn_info['system']))
				? $this->systems[$conn_info['system']]
				: [];

			// Combine all of the info into results
			$result[] = array_merge($system_info, $db_info, $user_info, $conn_info);
		}
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
 * @param string $database the name of the database information to load
 * @param array  $options  optional associative array of options
 * @return array  an associative arrays of connection info if it was
 *                loaded successfully, or NULL if none
 * @uses Yau\MDBAC\Config::fetchAll()
 */
public function fetchOne($database, array $options = [])
{
	$info = $this->fetchAll($database, $options);
	return array_shift($info);
}

/**
 * Fetch all connection information for a database and return a result set object
 *
 * @param string $database the name of the database information to load
 * @param array  $options  optional associative array of options
 * @return object a Yau\MDBAC\Result result set object
 * @uses Yau\MDBAC\Config::query()
 */
public function query($database, array $options = [])
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
 * Parse a config JSON for syntax errors
 *
 * Throws exceptions for the following:
 * - Missing or unreadable file
 * - Invalid XML syntax
 *
 * Trigger errors for the following:
 * - Bad values for some tags
 *
 * @param mixed  $json  the raw JSON string
 * @param string $error optional variable to store the error string
 * @return bool true if the xml passes inspection, or false if it does not
 */
public static function isValidJson($json, &$error = null)
{
	try
	{
		$data = json_decode($json, true, 32, JSON_THROW_ON_ERROR);
		if (empty($data))
		{
			throw new Exception('Blank JSON config');
		}
		elseif (!is_array($data))
		{
			throw new Exception('Invalid JSON config');
		}

		/*
		* Check system hosts
		*/
		if (empty($data['system']))
		{
			throw new Exception('No systems defined');
		}
		$system_hosts = [];
		foreach ($data['system'] as $name => $system)
		{
			$host = $system['host'] ?? '';

			if (empty($name))
			{
				throw new Exception('Empty system label for ' . $host);
			}
			if (!preg_match('/^\w+$/', $name))
			{
				throw new Exception("System name {$name} not alphanumeric");
			}
			if (empty($host))
			{
				throw new Exception('Empty system host for ' . $name);
			}
			if (!preg_match(self::$HOST_PATTERN, $host))
			{
				throw new Exception('Bad system host ' . $host);
			}

			// Check optional attributes if any
			if (isset($system['access']))
			{
				$access = $system['access'];
				if (strcmp($access, 'read') !=0 && strcmp($access, 'write') != 0)
				{
					throw new Exception("Invalid system access of {$access} for {$name}");
				}
			}
			if (isset($system['port']))
			{
				$port = $system['port'];
				if (!preg_match('/^\d+$/', $port))
				{
					throw new Exception("Invalid port {$port} for {$host}");
				}
			}

			// Make sure that names are not duplicated
			if (isset($system_hosts[$name]))
			{
				throw new Exception('Duplicate system name ' . $name);
			}
			$system_hosts[$name] = $name;
		}

		/*
		* Check databases
		*/
		if (!isset($data['database']))
		{
			throw new Exception('No databases defined');
		}
		$db_names = [];
		foreach ($data['database'] as $name => $database)
		{
			// Check database name
			if (!preg_match('/^\w+$/', $name))
			{
				throw new Exception("Database name {$name} not alphanumeric");
			}

			// Check that database name are not duplicated
			if (isset($db_names[$name]))
			{
				throw new Exception("Duplicate database name {$name}");
			}
			$db_names[$name] = $name;

			/*
			* Check each user set
			*/
			$defaults = 0;
			$user_names = [];
			foreach (($database['user'] ?? []) as $user)
			{
				// Check each user
				if (empty($user['username']))
				{
					throw new Exception("No username defined for a {$name} user");
				}
				$username = $user['username'];

				// Make sure that usernames are not duplicated for each database
				if (isset($user_names[$username]))
				{
					throw new Exception("Duplicate username for {$name}");
				}
				$user_names[$username] = $username;

				// Make sure there's only one default user
				if (!empty($user['default']) && ++$defaults > 1)
				{
					throw new Exception("More than one username default for {$name}");
				}
			}

			/*
			* Check each connection set
			*/
			$defaults = 0;
			$system_names = [];
			foreach (($database['connection'] ?? []) as $connection)
			{
				// Check that connection is to a valid system
				if (empty($connection['system']))
				{
					throw new Exception("A connection for {$name} has no system");
				}
				$system = $connection['system'];
				if (!isset($system_hosts[$system]))
				{
					throw new Exception("System {$system} for {$name} not defined");
				}

				// Check that systems are not duplicated
				if (isset($system_names[$system]))
				{
					throw new Exception("System {$system} used more than once for {$name}");
				}
				$system_names[$system] = $system;

				// Check weight
				if (isset($connection['weight']))
				{
					$weight = (string) $connection['weight'];
					if (!preg_match('/^\d+%?$/', $weight))
					{
						throw new Exception("Invalid connection weight {$weight} for system {$system}");
					}
				}

				// Make sure that there's only one default connection
				if (!empty($connection['default']) && ++$defaults > 1)
				{
					throw new Exception("More than one connection default for {$name}");
				}
			}
		}

		// Return true if all checks pass
		return true;
	}
	catch (Exception $e)
	{
		$error = $e->getMessage();
		return false;
	}
}

/**
 * Parse a config XML for syntax errors
 *
 * Throws exceptions for the following:
 * - Missing or unreadable file
 * - Invalid XML syntax
 *
 * Trigger errors for the following:
 * - Bad values for some tags
 *
 * @param mixed  $xml   either the raw XML string or a SimpleXML object
 * @param string $error optional variable to store the error string
 * @return bool true if the xml passes inspection, or false if it does not
 * @see SimpleXML
 * @todo Add DTD validation
 */
public static function isValidXml($xml, &$error = null)
{
	try
	{
		// Check argument that's passed
		if (is_string($xml))
		{
			// Check for proper xml
			if (substr($xml, 0, 5) != '<?xml')
			{
				throw new Exception('No XML header found');
			}
			elseif (!preg_match(self::$XML_PATTERN, $xml))
			{
				throw new Exception('Invalid XML');
			}

			// If a string, then parse it
			$use_errors = libxml_use_internal_errors(true);
			$xml = simplexml_load_string($xml);
			libxml_use_internal_errors($use_errors);
			if (empty($xml))
			{
				throw new Exception(($error = libxml_get_last_error()) ? $error->message : 'Unable to parse XML');
			}
		}
		elseif (is_object($xml))
		{
			// If object, then check the class
			if (!($xml instanceof SimpleXMLElement))
			{
				throw new Exception('Object not an instance of SimpleXMLElement');
			}
		}
		else
		{
			throw new Exception('Invalid XML type ' . gettype($xml));
		}

		/*
		* Check system hosts
		*/
		$system_hosts = [];
		foreach ($xml->xpath('systems/system') as $system)
		{
			// Get system name and host
			$name = (string) ($system['name'] ?? '');
			$host = (string) ($system['host'] ?? '');

			// Check that system name and host are not empty
			if (empty($name))
			{
				throw new Exception('Empty system label for ' . $host);
			}
			if (empty($host))
			{
				throw new Exception('Empty system host for ' . $name);
			}

			// Check system name and host
			if (!preg_match('/^\w+$/', $name))
			{
				throw new Exception("System name {$name} not alphanumeric");
			}
			if (!preg_match(self::$HOST_PATTERN, $host))
			{
				throw new Exception('Bad system host ' . $host);
			}

			// Check optional attributes if any
			if (isset($system['access']))
			{
				$access = (string) $system['access'];
				if (strcmp($access, 'read') != 0 && strcmp($access, 'write') != 0)
				{
					throw new Exception("Invalid system access of {$access} for {$name}");
				}
			}
			if (isset($system['port']))
			{
				$port = (string) $system['port'];
				if (!preg_match('/^\d+$/', $port))
				{
					throw new Exception("Invalid port {$port} for {$host}");
				}
			}

			// Make sure that names are not duplicated
			if (isset($system_hosts[$name]))
			{
				throw new Exception('Duplicate system name ' . $name);
			}
			$system_hosts[$name] = $name;
		}

		/*
		* Check databases
		*/
		$db_names = [];
		foreach ($xml->xpath('databases/database') as $database)
		{
			// Check database name
			if (empty($database['name']))
			{
				throw new Exception('A database without a name found');
			}
			$name = (string) $database['name'];
			if (!preg_match('/^\w+$/', $name))
			{
				throw new Exception("Database name {$name} not alphanumeric");
			}

			// Check that database name are not duplicated
			if (isset($db_names[$name]))
			{
				throw new Exception("Duplicate database name {$name}");
			}
			$db_names[$name] = $name;

			/*
			* Check each user set
			*/
			$defaults = 0;
			$user_names = [];
			foreach ($database->xpath('users/user') as $user)
			{
				// Check each user
				if (empty($user['username']))
				{
					throw new Exception("No username defined for a {$name} user");
				}
				$username = (string) $user['username'];

				// Make sure that usernames are not duplicated for each database
				if (isset($user_names[$username]))
				{
					throw new Exception("Duplicate username for {$name}");
				}
				$user_names[$username] = $username;

				// Make sure there's only one default user
				if (!empty($user['default']))
				{
					if (++$defaults > 1)
					{
						throw new Exception("More than one username default for {$name}");
					}
				}
			}

			/*
			* Check each connection set
			*/
			$defaults = 0;
			$system_names = [];
			foreach ($database->xpath('connections/connection') as $connection)
			{
				// Check that connection is to a valid system
				if (empty($connection['system']))
				{
					throw new Exception("A connection for {$name} has no system");
				}
				$system = (string) $connection['system'];
				if (!isset($system_hosts[$system]))
				{
					throw new Exception("System {$system} for {$name} not defined");
				}

				// Check that systems are not duplicated
				if (isset($system_names[$system]))
				{
					throw new Exception("System {$system} used more than once for {$name}");
				}
				$system_names[$system] = $system;

				// Check weight
				if (isset($connection['weight']))
				{
					$weight = (string) $connection['weight'];
					if (!preg_match('/^\d+%?$/', $weight))
					{
						throw new Exception("Invalid connection weight {$weight} for system {$system}");
					}
				}

				// Make sure that there's only one default connection
				if (!empty($connection['default']) && ++$defaults > 1)
				{
					throw new Exception("More than one connection default for {$name}");
				}
			}
		}

		// Return true if all checks pass
		return true;
	}
	catch (Exception $e)
	{
		$error = $e->getMessage();
		return false;
	}
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
 * @param string $filename the path to the config file
 * @param string $error    optional variable to store the error string
 * @return bool true if the file passes inspection, or false if it does not
 */
public static function isValidFile($filename, &$error = null)
{
	try
	{
		// Check file
		if (!file_exists($filename))
		{
			throw new Exception("Config file {$filename} not found");
		}
		if (!is_readable($filename))
		{
			throw new Exception("Unable to read config file {$filename}");
		}
		if (filesize($filename) < 10)
		{
			throw new Exception("Config file {$filename} is too small");
		}

		// Load file
		$cfg = file_get_contents($filename);
	}
	catch (Exception $e)
	{
		$error = $e->getMessage();
		return false;
	}

	// Check the config
	if (preg_match(self::$XML_PATTERN, $cfg))
	{
		return self::isValidXml($cfg, $error);
	}
	elseif (preg_match(self::$JSON_PATTERN, $cfg))
	{
		return self::isValidJson($cfg, $error);
	}
	else
	{
		$error = 'Invalid configuration file';
		return false;
	}
}

/*=======================================================*/
}

