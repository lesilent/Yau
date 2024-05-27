<?php

namespace Yau\Mutex\Adapter;

use Yau\Mutex\AdapterInterface;
use Yau\Db\Adapter\Driver\AbstractDriver;
use Yau\Db\Adapter\Adapter;
use InvalidArgumentException;
use RuntimeException;

/**
* A class used to ensure that only a single process of a script is running
*
* This class uses a database table to ensure that only a single instance of a
* script is running. This is useful for when crons are running on multiple
* machines for extra redundancy.
*
* Example
* <code>
* // Load class
* use Yau\Mutex\Mutex;
*
* // Open database connection
* $dbh = new PDO('mysql:host=localhost;dbname=test', $user, $pass);
* $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
*
* // Instantiate object with max process time of one day
* $options = ['max_process_time'=>86400];
* $mutex = Mutex::factory('mysql', $dbh, 'myscript', $options);
*
* if ($mutex->acquire())
* {
*     // Acquired right to process, so begin processing here
*
*     // Release right to process
*     $mutex->release();
* }
* </code>
*
* The MySQL table used to support this class requires at least 3 columns:
* <ul>
* <li>name - the name of the script or process
* <li>connection_id - the connection id that currently is running the script
* <li>update_date - the last update date/time for the connection
* </ul>
*
* Example schema
* <code>
* CREATE TABLE yau_mutex (
*   name varchar(255) NOT NULL default '',
*   connection_id int unsigned NOT NULL,
*   update_date timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
*   PRIMARY KEY (name),
*   KEY mutex_connid_idx (connection_id),
*   KEY mutex_date_idx (update_date)
* );
* </code>
*
* Code that uses this module should call release() method when they exit in
* order to delete the row in the mutex table. This will not only keep the
* table small, but will also speed up acquisition the next time around.
*
* Example of using max_time_func option
* <code>
* // Define callback function
* function notify_admin($seconds)
* {
*     $message = 'Processing time was exceeded by ' . $seconds . ' seconds';
*     mail('admin@mydomain.net', 'Process Error', $message);
* }
*
* // Instantiate object
* use Yau\Mutex\Mutex;
*
* // Open database connection
* $dbh = new PDO('mysql:host=localhost;dbname=test', $user, $pass);
* $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
*
* $options = array('name'=>'myscript', 'max_time_func'=>'notify_admin');
* $mutex = new Mutex::factory('mysql', $dbh, $options);
* </code>
*
* ChangeLog
* <ul>
* </ul>
*
* @author John Yau
*/
class Mysql implements AdapterInterface
{
/*=======================================================*/

/**
* The mutex name
*
* @var string
*/
private $name;

/**
* The database connection
*
* @var object
*/
private $dbh;

/**
* The mutex table
*
* @var string
*/
private $table;

/**
* The current connection id
*
* @var integer
*/
private $connection_id;

/**
* The current database user
*
* @var string
*/
private $current_user;

/**
* Queries used to interact with mutex table
*
* @var string
*/
private $select_sql;
private $insert_sql;
private $update_sql;
private $delete_sql;

/**
* Array to store values for some queries
*
* @var array
*/
private $sql_values = [];

/**
* Associative array of options for process
*
* Options:
* <pre>
* - max_process_time  integer the maximum time for a process in seconds. The
*                             default is 3600, or one hour.
* - max_time_func     mixed   the callback function to call when a process
*                             exceeds the maximum process time. The number of
*                             seconds that was exceeded will be passed to the
*                             function.
* - skip_table_check  boolean flag for skipping the initial mutex table check.
*                             This can improve performance slightly if you're
*                             confident that the table is set up correctly.
*                             The default is FALSE.
* - db_name           string  optional name of the database where the mutex
*                             table is located; if omitted, then it's the
*                             current database
* - table_name        string  the name of the mutex table
* - name_column       string  optional name of the column in the mutex table
*                             that represents the mutex name; default is "name"
* - connid_column     string  optional name of the column in the mutex table
*                             that represents the connection id; default is
*                             "connection_id"
* - timestamp_column  string  optional name of the column in the mutex table
*                             that represents the timestamp; default is
*                             "update_date"
* - timestamp_alias   string  optional name of the alias used to store the
*                             the current unix timestamp returned by MySQL;
*                             default is "_timestamp"
* - updatestamp_alias string  optional name of the alias used to store the
*                             timestamp column value as a unix timestamp;
*                             default is "_updatestamp"
* - max_name_length   integer the maximum allowed length for mutex name,
*                             anything over this length will be truncated;
*                             default is 255
* </pre>
*
* @var array
*/
private $options = [
	'max_process_time'  => 3600,
	'max_time_func'     => null,
	'skip_table_check'  => false,
	'db_name'           => null,
	'table_name'        => 'yau_mutex',
	'name_column'       => 'name',
	'connid_column'     => 'connection_id',
	'timestamp_column'  => 'update_date',
	'timestamp_alias'   => '_timestamp',
	'updatestamp_alias' => '_updatestamp',
	'max_name_length'   => 255,
	'include_hostname'  => true,
];

/**
 * Array of required options that cannot be empty
 *
 * @var array
 */
private static $REQUIRED_OPTIONS = [
	'table_name',
	'name_column',
	'connid_column',
	'timestamp_column',
	'timestamp_alias',
	'updatestamp_alias',
];

/**
* Constructor
* Options:
* <pre>
* - max_process_time integer the maximum time for a process in seconds. If a
*                            process exceeds this time, then it will be killed.
*                            The default is one hour.
* - max_time_func    mixed   the callback function to call when a process
*                            exceeds the maximum time
* </pre>
*
* @param mixed $dbh     a mysql database connection object or resource
* @param array $options optional associative array of options
* @throws Exception if there's an error with the arguments
*/
public function __construct($dbh, array $options = [])
{
	// Store process options
	if (!empty($options))
	{
		$this->options = array_merge($this->options, $options);
	}

	// Store name
	if (empty($options['name']))
	{
		$trace = debug_backtrace();
		$options['name'] = $trace[0]['file'];
	}
	$this->name = $options['name'];

	// Check whether callback function is callable or not
	if (!empty($this->options['max_time_func'])
		&& !is_callable($this->options['max_time_func']))
	{
		throw new InvalidArgumentException('Callback function is not callable');
	}

	// Check options
	foreach (self::$REQUIRED_OPTIONS as $opt)
	{
		if (empty($this->options[$opt]))
		{
			throw new InvalidArgumentException('Option ' . $opt . ' is empty');
		}
	}

	// Check that maximum name length is valid
	$this->options['max_name_length'] = intval($this->options['max_name_length']);
	if ($this->options['max_name_length'] < 0)
	{
		throw new InvalidArgumentException('Invalid maximum name length');
	}

	// Truncate name to maxmimum allowed length
	if (!empty($this->options['max_name_length']))
	{
		$this->name = substr($this->name, 0, $this->options['max_name_length']);
	}

	// Check that database connection is MySQL
	$driver = Adapter::getDriver(($dbh instanceof AbstractDriver) ? $dbh->getConnection() : $dbh);
	if (empty($driver) || stripos($driver, 'mysql') === false)
	{
		throw new InvalidArgumentException('Database connection is not MySQL');
	}

	// Wrap database connection
	$this->dbh = Adapter::factory($dbh);

	// Check mutex table
	if (empty($this->options['skip_table_check']))
	{
		$this->checkMutexTable();
	}

	// Get the current connection id and current user
	$row = $this->dbh->getAssocRow('SELECT CONNECTION_ID() AS connid, USER() AS curuser');
	if (empty($row))
	{
		throw new RuntimeException('Unable to obtain current connection id and user');
	}
	$this->connection_id = $row['connid'];
	$this->current_user  = $row['curuser'];

	// Prepare queries for mutex table
	$this->prepareQueries();
}

/**
* Check mutex table exists and has the required columns
*
* @throws Exception if a requirement doesn't exist
*/
private function checkMutexTable()
{
	// Check whether mutex table exists
	$sql = 'SHOW TABLES'
	     . (empty($this->options['db_name']) ? '' : ' FROM ' . $this->options['db_name'])
	     . ' LIKE ?';
	$table = $this->dbh->getOne($sql, array($this->options['table_name']));
	if (empty($table))
	{
		throw new InvalidArgumentException('Unable to locate mutex table');
	}

	// Form table name
	$this->table = (empty($this->options['db_name']) ? '' : $this->options['db_name'] . '.')
		. $this->options['table_name'];

	// Fetch column names and types in table
	$columns = array();
	$have_keys = FALSE;
	$sth = $this->dbh->query('SHOW COLUMNS FROM ' . $this->table);
	while ($row = $sth->fetchAssocRow())
	{
		// Store name and type
		$columns[$row['Field']] = $row['Type'];
	}

	// Check required columns exist
	$required_columns = array(
		'name'      => 'name',
		'connid'    => 'connection id',
		'timestamp' => 'timestamp'
	);
	foreach ($required_columns as $prefix => $name)
	{
		if (empty($columns[$this->options[$prefix . '_column']]))
		{
			throw new InvalidArgumentException('Unable to locate ' . $name. ' column in mutex table');
		}
	}
}

/**
* Prepare queries
*/
private function prepareQueries()
{
	// Prepare main queries
	$columns = array($this->options['name_column'], $this->options['connid_column'], $this->options['timestamp_column']);
	$this->insert_sql = 'INSERT IGNORE INTO ' . $this->table
		. ' (' . implode(', ', $columns) . ')'
		. ' VALUES (' . str_repeat('?, ', count($columns) - 1) . 'NOW())';

	$this->select_sql = 'SELECT *, UNIX_TIMESTAMP() AS ' . $this->options['timestamp_alias']
		. ' , UNIX_TIMESTAMP(' . $this->options['timestamp_column'] . ') AS ' . $this->options['updatestamp_alias']
		. ' FROM ' . $this->table
		. ' WHERE ' . $this->options['name_column'] . ' = ?'
		. ' LIMIT 1';

	$this->delete_sql = 'DELETE FROM ' . $this->table
	                  . ' WHERE ' . $this->options['name_column'] . ' = ?'
	                  . ' AND ' . $this->options['connid_column'] . ' = ?';

	$this->update_sql = 'UPDATE ' . $this->table
		. ' SET ' . $this->options['connid_column'] . ' = ?'
		. ' , ' . $this->options['timestamp_column'] . ' = NOW()'
		. ' WHERE ' . $this->options['name_column'] . ' = ?'
		. ' AND ' . $this->options['connid_column'] . ' = ?';

	// Prepare values
	$this->sql_values = array($this->name, $this->connection_id);
}

/**
* Return the connection id for the MySQL connection
*
* @return integer
*/
public function getConnectionId()
{
	return $this->connection_id;
}

/**
* Return whether a connection id currently exists in the MySQL process list
*
* @param  integer $connection_id the MySQL connection id
* @return boolean TRUE if connection exists, or FALSE if not
*/
protected function connectionExists($connection_id)
{
	$sth = $this->dbh->query('SHOW PROCESSLIST');
	while ($row = $sth->fetchAssocRow())
	{
		// Return TRUE if connection id was found
		if ($row['Id'] == $connection_id)
		{
			return TRUE;
		}
	}

	// Return FALSE if connection id not found
	return FALSE;
}

/**
* Acquire the right to begin processing
*
* This acquires the right to process by writing the current process id to
* a process file that was defined in the constructor.
*
* @return boolean true if acquisition was successful, otherwise false
* @throws Exception
*/
public function acquire():bool
{
	// Attempt to make acquisition
	if ($this->dbh->exec($this->insert_sql, $this->sql_values))
	{
		return true;
	}

	// If unable to make acquisition, read existing record
	$row = $this->dbh->getRow($this->select_sql, [$this->name]);
	if (empty($row))
	{
		// If no record exists, then possibly don't have INSERT privileges
		trigger_error('Possibly missing privileges to INSERT into '
			. $this->table . ' by ' . $this->current_user);
		return false;
	}

	// If connection id is the current one, then it's ok
	$connid = $row[$this->options['connid_column']];
	if ($connid == $this->connection_id)
	{
		return TRUE;
	}

	// Prepare update values
	$update_values = array($this->connection_id, $this->name, $connid);

	// If other connection doesn't exist, then go ahead and update/insert record to current id
	if (!$this->connectionExists($connid))
	{
		if ($this->dbh->exec($this->update_sql, $update_values) > 0
			|| $this->dbh->exec($this->insert_sql, $this->sql_values) > 0)
		{
			return TRUE;
		}
		trigger_error('Possibly missing privileges to UPDATE '
			. $this->table . ' by ' . $this->current_user);
		return FALSE;
	}

	// Check whether other connection exceeded maximum allowed time
	$exceeded_time = (empty($this->options['max_process_time']))
		? 0
		: $row[$this->options['timestamp_alias']]
			- $row[$this->options['updatestamp_alias']]
			- $this->options['max_process_time'];
	if ($exceeded_time <= 0)
	{
		// Return FALSE if other connection hasn't exceeded its limit
		return FALSE;
	}

	// Kill other connection if it exceeded maximum allowed time
	$this->dbh->exec('KILL ' . $connid);
	if ($this->connectionExists($connid))
	{
		// Unable to kill other connection
		trigger_error('Unable to kill connection id ' . $connid);
		return false;
	}

	// Insert or update record to current connection
	if ($this->dbh->exec($this->update_sql, $update_values) > 0
		|| $this->dbh->exec($this->insert_sql, $this->sql_values) > 0)
	{
		// Call max time function if there is one
		if (!empty($this->options['max_time_func']))
		{
			call_user_func($this->options['max_time_func'], $exceeded_time);
		}
		return true;
	}

	// Return false to indicate acquisition failed
	return false;
}

/**
* Truncate process file to indicate that processing is done
*
* @return bool true if process file was successfully released, or false if not
*/
public function release():bool
{
	// Prepare values
	$values = array($this->name, $this->connection_id);

	// Delete record from mutex table
	if ($this->dbh->exec($this->delete_sql, $this->sql_values) > 0)
	{
		return true;
	}

	// If no rows were deleted, then check whether record is still there
	// or has a different connection id
	return (($row = $this->dbh->getRow($this->select_sql, array($this->name))) === false
		|| $row[$this->options['connid_column']] != $this->connection_id);
}

/**
* Update timestamp in record to indicate script is still running properly
*
* @return bool true if update was sucessful, or false if not
*/
public function keepAlive():bool
{
	$values = [$this->connection_id, $this->name, $this->connection_id];
	return ($this->dbh->exec($this->update_sql, $values) > 0);
}

/*=======================================================*/
}

