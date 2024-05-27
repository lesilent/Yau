<?php declare(strict_types = 1);

namespace Yau\Db\Adapter\Driver;

use Yau\Db\Sql\Sql;
use Exception;

/**
 * A database wrapper object for interacting with databases
 *
 * This adapter class wraps a raw database connection object or resource to
 * provide a common set of methods for interacting with the database.
 *
 * Quick list of common methods:
 * <ul>
 * <li>getConnection()    - return the database connection object or resource
 * <li>prepare()          - prepare a statement and return object for it
 * <li>query()            - query database and return a result/statement object
 * <li>exec()             - execute a statement and return the number of
 *                          affected rows
 * <li>lastInsertId()     - return the id of the last inserted row
 * <li>getOne()           - execute a query and fetch the value from first
 *                          column of the first row
 * <li>getRow()           - fetch a single row from result set
 * <li>getAll()           - execute a query and fetch all results as an array
 * <li>beginTransaction() - begin a transaction
 * <li>commit()           - commit current transaction
 * <li>rollBack()         - rollback current transaction
 * <li>inTransaction()    - return whether a transaction is currently active
 * <li>disconnect()       - disconnect from database
 * </ul>
 *
 * Example
 * <code>
 * // $db can be a MySQL resource or PDO object
 * $dbh = Adapter::factory($db);
 *
 * // Call a common method
 * $query = 'SELECT age FROM people WHERE person_id = ?';
 * $age = $dbh->fetchOne($query, array(9));
 *
 * // Fetch results as name-value pairs
 * $query = 'SELECT person_id, lname, fname FROM person WHERE person_id = ?';
 * $person = $dbh->fetchRow($query, array(9));
 * echo 'Hello ', $person['fname'], ' ', $person['lname'];
 * </code>
 *
 * Subclasses need to implement the following abstract methods
 * <ul>
 * <li>exec()
 * <li>lastInsertId()
 * <li>beginTransaction()
 * <li>commit()
 * <li>rollBack()
 * <li>inTransaction()
 * <li>disconnect()
 * </ul>
 *
 * Definitions:
 * <ul>
 * <li>query - an SELECT statement that queries the database
 * <li>statement - a generic SQL statement that's not necessarily a SELECT
 * </ul>
 *
 * @author John Yau
 */
abstract class AbstractDriver
{
/*=======================================================*/

/**
 * The database connection object or resource
 *
 * @var mixed
 */
protected $dbh;

/**
 * Flag to store whether in a transaction or not
 *
 * @var bool
 */
protected $transaction = false;

/**
* Constructor
*
* @param mixed $dbh the database connection object or resource
*/
public function __construct($dbh)
{
	$this->dbh = $dbh;
}

//-------------------------------------

/**
* Return the current database connection object or resource
*
* @return mixed the current database connection object or resource, or false
*               if undefined
*/
public function getConnection()
{
	return $this->dbh ?? false;
}

/**
 * Prepare a SQL statement and return an object for it
 *
 * Example
 * <code>
 * $dbh = Adapter::factory($dbh);
 *
 * $sth = $dbh->prepare('SELECT lname FROM person WHERE person_id = ?');
 * $sth->execute(array(10));
 * while ($row = $sth->fetch())
 * {
 *     print_r($row);
 * }
 * </code>
 *
 * @param string $stmt the SQL statement to prepare
 * @return object a Yau\Db\Statement object
 * @see Yau\Db\Statement
 */
public function prepare($stmt)
{
	// Figure out statement subclass
	$class_name = str_replace('\\Adapter\\', '\\Statement\\', get_class($this));

	// Return new statement subclass
	$sth = new $class_name($this->dbh, $stmt);
	return $sth;
}

/**
 * Execute a SQL statement and return a result statement object
 *
 * @param  string $query  the SQL query to execute
 * @param  array  $params an array of parameters to bind to the statement
 * @return object a Yau\Db\Statement object, or FALSE on failure
 * @throws Exception if unable to prepare statement
 * @uses   Yau\Db\AbstractDriver::prepare()
 */
public function query($query, array $params = [])
{
	$sth = $this->prepare($query);
	if (empty($sth))
	{
		throw new Exception('Unable to prepare statement: ' . $query);
	}
	return ($sth->execute($params)) ? $sth : false;
}

//-------------------------------------
// Abstract execute methods

/**
 * Execute a SQL statement and return the number of rows affected
 *
 * @return integer the number of affected rows, or false if error
 */
abstract public function exec($stmt, array $params = []);

/**
 * Return the id of the last inserted row
 *
 * @return string the id of the last inserted row
 */
abstract public function lastInsertId();

//-------------------------------------
// Fetch methods

/**
 * Execute a SQL query and fetch the value from the column of the first row
 *
 * Example
 * <code>
 * $params = array(12);
 * $age = $dbh->getOne('SELECT age FROM person WHERE person_id = ?', $params);
 *
 * echo $age;
 * </code>
 *
 * @param string $query  the SQL query to execute
 * @param array  $params array of values to bind to placeholders
 * @return mixed  the value of the first column from the first row in the
 *                result, or false if there's no data
 */
public function getOne($query, array $params = [])
{
	return (($sth = $this->query($query, $params))
		&& ($row = $sth->fetchNumRow()))
		? reset($row) : false;
}

/**
 * Execute a SQL query and fetch the first row of the result set as an associative array
 *
 * Example
 * <code>
 * $query = 'SELECT fname, lname FROM person WHERE person_id = ?';
 * $params = array(12);
 * $row = $dbh->fetchRow($query, $params);
 * </code>
 *
 * @param string $query  the SQL query to execute
 * @param array  $params array of values to bind to placeholders
 * @return mixed  the first row of the result with the type depending on the
 *                fetch mode, or false if there's no data or error
 */
public function getAssocRow($query, array $params = [])
{
	return ($sth = $this->query($query, $params))
		? $sth->fetchAssocRow()
		: false;
}

/**
 * Execute a SQL query and fetch the first row of the result set as an numeric array
 *
 * Example
 * <code>
 * $query = 'SELECT fname, lname FROM person WHERE person_id = ?';
 * $params = array(12);
 * $row = $dbh->fetchRow($query, $params);
 * </code>
 *
 * @param string $query  the SQL query to execute
 * @param array  $params array of values to bind to placeholders
 * @return mixed  the first row of the result with the type depending on the
 *                fetch mode, or FALSE if there's no data or error
 */
public function getNumRow($query, array $params = [])
{
	return ($sth = $this->query($query, $params))
		? $sth->fetchNumRow()
		: false;
}

/**
 * Alias for getAssocRow method
 *
 * @param string $query  the SQL query to execute
 * @param array  $params array of values to bind to placeholders
 * @return mixed  the first row of the result with the type depending on the
 *                fetch mode, or FALSE if there's no data or error
 * @uses AbstractDriver::getAssocRow()
 */
public function getRow($query, array $params = [])
{
	return $this->getAssocRow($query, $params);
}

/**
 * Execute a SQL statement and fetch all results as an array
 *
 * Example
 * <code>
 * $query = 'SELECT fname, lname FROM person WHERE where age > ?';
 * $params = array(12);
 *
 * $people = $dbh->fetchAll($query, $params);
 * </code>
 *
 * @param string  $query     the SQL query to execute
 * @param array   $params    optional array of parameters to bind to query
 * @param integer $fetchmode the fetch
 * @return array array of results, with the type depending on the fetch mode
 */
public function getAll($query, array $params = [])
{
	return ($sth = $this->query($query, $params))
		? $sth->fetchAll()
		: FALSE;
}

//-------------------------------------
// Wrapper methods

/**
 * Wrapper method that allows for easy inserts into a table
 *
 * @param string $table  the name of the table
 * @param array  $params associative array of parameters
 * @return integer number of rows inserted, or false if error
 * @uses Sql::buildInsertStatement()
 */
public function insertInto($table, array $params)
{
	$sql = Sql::buildInsertStatement($table, $params);
	return $this->exec($sql, $params);
}

/**
 * Wrapper method that allows for easy updates of rows
 *
 * @param string $table  the name of the table
 * @param array  $params associative array of parameters
 * @param array  $where  associative array of where parameters
 * @return integer integer the number of affected rows, or FALSE if error
 * @uses Yau\Db\Sql::buildUpdateStatement()
 */
public function updateTable($table, array $params, array $where)
{
	$sql = Sql::buildUpdateStatement($table, $params, $where);
	return $this->exec($sql, array_merge(array_values($params), array_values($where)));
}

/**
 * Wrapper method that allows for easy deletion of rows
 *
 * @param string $table the name of the table
 * @param array  $where associative array of where parameters
 * @return integer integer the number of affected rows, or FALSE if error
 * @uses Yau\Db\Sql::buildDeleteStatement()
 */
public function deleteFrom($table, array $where)
{
	$sql = Sql::buildDeleteStatement($table, $where);
	return $this->exec($sql, $where);
}

//-------------------------------------
// Abstract transaction methods

/**
 * Begin a transaction
 *
 * @return bool true on success, or false on failure
 */
abstract public function beginTransaction();

/**
 * Commit current transaction
 *
 * @return bool true on success, or false on failure
 */
abstract public function commit();

/**
 * Rollback the current transaction
 *
 * @return bool true on success, or false on failure
 */
abstract public function rollBack();

/**
 * Return whether a transaction is currently active
 *
 * @return bool true if a transaction is currently active, or false if not
 */
public function inTransaction():bool
{
	return $this->transaction;
}

//-------------------------------------
// Miscellaneous abstract methods

/**
 * Disconnect from the database
 *
 * @return bool true on success, or false on failure
 */
abstract public function disconnect();

//-------------------------------------

/**
 * Magic method to route all methods to object
 *
 * @param string $func the object method to call
 * @param array  $args array of arguments for method
 * @return mixed
 */
public function __call($func, array $args = [])
{
	return call_user_func_array([$this->dbh, $func], $args);
}

/*=======================================================*/
}
