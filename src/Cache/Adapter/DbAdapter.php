<?php declare(strict_types=1);

namespace Yau\Cache\Adapter;

use Yau\Db\Adapter\Adapter;
use Yau\Db\Connect\Connect;
use Yau\Cache\Adapter\AbstractAdapter;
use InvalidArgumentException;
use DomainException;

/**
 * Cache that uses database
 */
class DbAdapter extends AbstractAdapter
{
/*=======================================================*/

/**
 * Name of the table
 *
 * @var string
 */
private $table = 'cache_items';

/**
 * The name of the id column
 *
 * @var string
 */
private $id_col = 'item_id';

/**
 * The name of the data column
 *
 * @var string
 */
private $data_col = 'item_data';

/**
 * The name of the expire column
 *
 * @var string
 */
private $expire_col = 'item_expire';

/**
 * Database driver
 *
 * @var string
 */
private $driver;

/**
 * Database handler
 *
 * @var object
 */
private $dbh;

/**
 * Flag for whether cache table was created
 *
 * @var bool
 */
private $created = false;

/**
 * Constructor
 *
 * @param array $params
 * @throws InvalidArgumentException
 */
public function __construct(array $params = [])
{
	if (empty($params['dbh']))
	{
		// If no database object, then we're passing database parameters
		if (empty($params['driver']))
		{
			throw new InvalidArgumentException('No driver specified');
		}
		$this->dbh = fn() => Connect::factory('PDO', $params);
	}
	elseif (is_callable($params['dbh']))
	{
		$this->dbh = $params['dbh'];
	}
	else
	{
		$this->setConnection($params['dbh']);
	}
	foreach (['table', 'id_col', 'data_col', 'expire_col'] as $option)
	{
		if (!empty($params[$option]))
		{
			$this->$option = $params[$option];
		}
	}
	parent::__construct($params);

	// Trigger error in 2038
	if (time() > 2145942000)
	{
		trigger_error('createTable needs to be updated to avoid the year 2028 problem', E_USER_DEPRECATED);
	}
}

/**
 * Set database connection and driver for the current object
 *
 * @param mixed $dbh
 * @throws InvalidArgumentException if invalid driver
 */
private function setConnection($dbh): void
{
	$dbh = Adapter::factory($dbh);
	if (!preg_match('/\b(\w+)?$/', get_class($dbh), $matches))
	{
		throw new InvalidArgumentException('Invalid driver');
	}
	$this->dbh = $dbh;
	$this->driver = preg_match('/^mysql/i', $matches[1]) ? 'mysql' : strtolower($matches[1]);
	$this->createTable();
}

/**
 * Return database connection
 *
 * @return object
 */
private function getConnection(): object
{
	if (empty($this->driver))
	{
		$dbh = call_user_func($this->dbh);
		$this->setConnection($dbh);
	}
	return $this->dbh;
}

/**
 * Create cache table
 *
 * @param bool $drop drop table prior to creating
 * @return bool
 * @throws DomainException if driver isn't supported
 */
public function createTable($drop = false): bool
{
	$dbh = $this->getConnection();
	switch ($this->driver)
	{
		case 'mysql':
			$sql = ($drop ? "DROP TABLE IF EXISTS {$this->table};" : '')
				. "CREATE TABLE IF NOT EXISTS {$this->table} ({$this->id_col} varbinary(255) NOT NULL PRIMARY KEY, {$this->data_col} mediumblob NOT NULL, {$this->expire_col} int unsigned) COLLATE utf8mb4_bin, ENGINE = InnoDB";
			break;
		case 'sqlsrv':
			$sql = ($drop ? "IF EXISTS (SELECT 1 FROM sysobjects WHERE id = object_id(N'[dbo].{$this->table}') AND OBJECTPROPERTY(id, N'IsUserTable') = 1) DROP TABLE {$this->table};" : '')
				. "IF NOT EXISTS (SELECT 1 FROM sysobjects WHERE id = object_id(N'[dbo].{$this->table}') AND OBJECTPROPERTY(id, N'IsUserTable') = 1) CREATE TABLE {$this->table} ({$this->id_col} varchar(255) NOT NULL PRIMARY KEY, {$this->data_col} varbinary(max) NOT NULL, {$this->expire_col} int)";
			break;
		default:
			throw new DomainException('No createTable support for ' . $this->driver);
	}
	$dbh->exec($sql);
	return true;
}

/**
 * Drop cache table
 */
public function dropTable()
{
	$dbh = $this->getConnection();
	switch ($this->driver)
	{
		case 'mysql':
			$sql = "DROP TABLE IF EXISTS {$this->table};";
			break;
		case 'sqlsrv':
			$sql = "IF EXISTS (SELECT 1 FROM sysobjects WHERE id = object_id(N'[dbo].{$this->table}') AND OBJECTPROPERTY(id, N'IsUserTable') = 1) DROP TABLE {$this->table};";
			break;
		default:
			throw new DomainException('No dropTable support for ' . $this->driver);
	}
	$dbh->exec($sql);
}

/**
 * Fetch a value from the cache
 *
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
public function get($key, $default = null)
{
	$this->created = ($this->created || $this->createTable() || true);  // @phpstan-ignore-line
	switch ($this->driver)
	{
		case 'mysql':
			$sql = "SELECT {$this->data_col} FROM {$this->table} WHERE {$this->id_col} = ? AND ({$this->expire_col} IS NULL OR {$this->expire_col} >= ?) LIMIT 1";
			break;
		case 'sqlsrv':
			$sql = "SELECT TOP(1) {$this->data_col} FROM {$this->table} WHERE {$this->id_col} = ? AND ({$this->expire_col} IS NULL OR {$this->expire_col} >= ?)";
			break;
		default:
			throw new DomainException('No get support for ' . $this->driver);
	}
	if ($row = $this->getConnection()->getRow($sql, [$this->hashKey($key), time()]))
	{
		return $this->decodeValue(reset($row));
	}
	else
	{
		return ($default instanceof \Closure) ? call_user_func($default) : $default;
	}
}

/**
 * Store a value in the cache
 *
 * @param string $key
 * @param mixed $value
 * @param null|int|\DateInterval $ttl
 * @return bool
 */
public function set($key, $value, $ttl = null)
{
	$this->created = ($this->created || $this->createTable() || true);  // @phpstan-ignore-line
	switch ($this->driver)
	{
		case 'mysql':
			$sql = "INSERT INTO {$this->table}"
				. " ({$this->id_col}, {$this->data_col}, {$this->expire_col})"
				. " VALUES(?,?,?) ON DUPLICATE KEY"
				. " UPDATE {$this->data_col} = VALUES({$this->data_col}), {$this->expire_col} = VALUES({$this->expire_col})";
			break;
		case 'sqlsrv':
			$sql = "MERGE INTO {$this->table} USING (VALUES (?, ?, ?)) AS MergeSource"
				. " ({$this->id_col}, {$this->data_col}, ($this->expire_col})"
				. " ON {$this->table}.{$this->id_col} = MergeSource.{$this->id_col}"
				. " WHEN MATCHED THEN"
				. " UPDATE SET {$this->data_col} = MergeSource.{$this->data_col}, {$this->expire_col} = MergeSource.{$this->expire_col}"
				. " WHEN NOT MATCHED THEN"
				. " INSERT ({$this->id_col}, {$this->data_col}, {$this->expire_col})"
				. " VALUES (MergeSource.{$this->id_col}, MergeSource.{$this->data_col}, {$this->expire_col});";
			break;
		default:
			throw new DomainException('No set support for ' . $this->driver);
	}
	$values = [$this->hashKey($key), $this->encodeValue($value), $this->getTimestampForTTL($ttl)];
	$this->getConnection()->exec($sql, $values);
	return true;
}

/**
 * Delete a value in the cache
 *
 * @param string $key
 * @return bool
 */
public function delete($key)
{
	$sql = "DELETE FROM {$this->table} WHERE {$this->id_col} = ?";
	$this->getConnection()->exec($sql, [$this->hashKey($key)]);
	return true;
}

/**
 * Clear the entire cache
 *
 * @return bool true on success and false on failure
 */
public function clear()
{
	$sql = "TRUNCATE TABLE {$this->table}";
	$this->getConnection()->exec($sql);
	return true;
}

/**
 * Return whether an item exists in the cache or not
 *
 * @param string $key
 * @return bool
 */
public function has($key): bool
{
	$this->created = ($this->created || $this->createTable() || true);  // @phpstan-ignore-line
	switch ($this->driver)
	{
		case 'mysql':
			$sql = "SELECT 1 FROM {$this->table} WHERE {$this->id_col} = ? AND ({$this->expire_col} IS NULL OR {$this->expire_col} >= ?) LIMIT 1";
			break;
		case 'sql':
			$sql = "SELECT TOP(1) 1 FROM {$this->table} WHERE {$this->id_col} = ? AND ({$this->expire_col} IS NULL OR {$this->expire_col} >= ?)";
			break;
		default:
			throw new DomainException('No has support for ' . $this->driver);
	}
	return $this->getConnection()->getOne($sql, [$this->hashKey($key), time()]) ? true : false;
}

/*=======================================================*/
}
