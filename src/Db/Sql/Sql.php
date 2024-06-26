<?php declare(strict_types = 1);

namespace Yau\Db\Sql;

use Exception;

/**
 * Abstract class for building simple SQL clauses and statements
 *
 * @author John Yau
 * @todo add support for automatically escaping values when forming statements
 */
abstract class Sql
{
/*=======================================================*/

/**
 * Form an IN predicate with placeholders
 *
 * Example
 * <code>
 * use Yau\Db\Sql\Sql;
 * $names = ['John', 'Jane', 'Jimmy'];
 * $clause = Sql::buildInPredicate('fname', $names);
 * echo $clause;
 * </code>
 *
 * Output from above example
 * <code>
 * fname IN (?, ?, ?)
 * </code>
 *
 * @param string $column the name of the column
 * @param array  $values
 * @return string
 */
public static function buildInPredicate($column, array $values):string
{
	return $column . ' IN (?' . str_repeat(', ?', count($values) - 1) . ')';
}

/**
 * Form an NOT IN predicate with placeholders
 *
 * Example
 * <code>
 * use Yau\Db\Sql\Sql;
 * $names = ['John', 'Jane', 'Jimmy'];
 * $clause = Sql::buildNotInPredicate('fname', $names);
 * echo $clause;
 * </code>
 *
 * Output from above example
 * <code>
 * fname NOT IN (?, ?, ?)
 * </code>
 *
 * @param string $column the name of the column
 * @param array  $values
 * @return string
 */
public static function buildNotInPredicate($column, array $values):string
{
	return $column . ' NOT IN (?' . str_repeat(', ?', count($values) - 1) . ')';
}

/**
 * Form a WHERE clause with placeholders
 *
 * Example
 * <code>
 * use Yau\Db\Sql\Sql;
 *
 * $where = [
 *     'fname' => 'John',
 *     'lname' => 'Doe'
 * ];
 *
 * $clause = Sql::buildWhereClause($where);
 * echo $clause;
 * </code>
 *
 * Output from above example
 * <code>
 * WHERE fname = ? AND lname = ?
 * </code>
 *
 * @param mixed $where either a string or associative array of values for the
 *                     WHERE clause
 * @return string
 */
public static function buildWhereClause($where):string
{
	return ' WHERE ' . (is_array($where)
		? implode(' = ? AND ', array_keys($where)) . ' = ?'
		: strval($where));
}

/**
 * Form a WHERE column IN clause with placeholders
 *
 * Example
 * <code>
 * $names = ['John', 'Jane', 'Jimmy'];
 * $clause = Sql::buildWhereInClause('fname', $names);
 * echo $clause;
 * </code>
 *
 * Output from above example
 * <code>
 * WHERE fname IN (?, ?, ?)
 * </code>
 *
 * @param string $column the name of the column
 * @param array  $values
 * @return string
 */
public static function buildWhereInClause($column, array $values):string
{
	return ' WHERE ' . self::buildInPredicate($column, $values);
}

/**
 * Build a simple SELECT statement with placeholders
 *
 * @param string $columns optional associative array of columns to select; if
 *                        omitted, then all columns will be returned
 * @param string $table   the table to select from
 * @param mixed  $where   either a string or array representing the WHERE
 *                        clause of rows to select
 * @return string  the SELECT statement with placeholders
 * @uses Sql::buildWhereClause()
 */
public static function buildSelectStatement($columns, $table, $where):string
{
	// Determine which columns to select
	if (empty($columns))
	{
		$columns = '*';
	}
	else
	{
		$columns = (is_array($columns))
			? implode(', ', $columns)
			: strval($columns);
	}

	// Return SELECT statement
	return 'SELECT ' . $columns
		. ' FROM ' . $table
		. self::buildWhereClause($where);
}

/**
 * Build a simple INSERT statement with placeholders
 *
 * Example
 * <code>
 * $table = 'person';
 * $params = [
 *     'fname' => 'John',
 *     'lname' => 'Doe',
 *     'age'   => 22
 * ];
 *
 * $stmt = Sql::buildInsertStatement($table, $params);
 * echo $stmt;
 * </code>
 *
 * Output from above example
 * <code>
 * INSERT INTO person (fname, lname, age) VALUES(?, ?, ?)
 * </code>
 *
 * @param  string $table  the name of the table to insert into
 * @param  array  $params associative array of parameters
 * @return string the INSERT statement with placeholders
 */
public static function buildInsertStatement($table, array $params):string
{
	return 'INSERT INTO ' . $table
		. ' (' . implode(', ', array_keys($params)) . ')'
		. ' VALUES(?' . str_repeat(', ?', count($params) - 1) . ')';
}

/**
 * Build a simple UPDATE statement with placeholders
 *
 * Example
 * <code>
 * $table = 'person';
 * $params = [
 *     'fname' => 'John',
 *     'lname' => 'Doe',
 *     'age'   => 22
 * ];
 * $where = [
 *     'person_id' => 123
 * ];
 *
 * $stmt = Sql::buildInsertStatement($table, $params, $where);
 * echo $stmt;
 * </code>
 *
 * Output from above example
 * <code>
 * UPDATE person SET fname = ?, lname = ?, age = ? WHERE person_id = ?
 * </code>
 *
 * @param string $table  the name of the table to update
 * @param array  $params associative array of parameters
 * @param mixed  $where  either a string or associative array of values for the
 *                        WHERE clause
 * @return string the UPDATE statement with placeholders
 * @uses Sql::buildWhereClause()
 */
public static function buildUpdateStatement($table, array $params, $where):string
{
	return 'UPDATE ' . $table
		. ' SET ' . implode(' = ?,', array_keys($params)) . ' = ?'
		. self::buildWhereClause($where);
}

/**
 * Build statement with placeholders for deleting record
 *
 * Example
 * <code>
 * $table = 'person';
 * $where = [
 *     'fname' => 'John',
 *     'age'   => 22
 * ];
 *
 * $stmt = Sql::buildDeleteStatement($table, $where);
 * echo $stmt;
 * </code>
 *
 * Output from above example
 * <code>
 * DELETE FROM person WHERE fname = ? AND age = ?
 * </code>
 *
 * @param string $table the table to delete from
 * @param mixed  $where either a string or associative array of values for the
 *                      WHERE clause
 * @return string the DELETE statement with placeholders
 * @uses Sql::buildWhereClause()
 */
public static function buildDeleteStatement($table, $where):string
{
	return 'DELETE FROM ' . $table . self::buildWhereClause($where);
}

/**
 * Build a SQL statement by replacing placeholders with values
 *
 * Example
 * <code>
 * $params = ['John', 18];
 * $query = 'SELECT lname FROM people WHERE fname = ? AND age > ?';
 *
 * $query = Sql::replacePlaceholders($query, $params);
 * mysql_query($query);
 * </code>
 *
 * @param string $stmt   the SQL statement with placeholders
 * @param array  $params array of input parameters to replace placeholders
 *                       with
 * @param mixed  $escape the callback function used to escape strings;
 *                       default is addslashes
 * @return string a SQL statement with values in it
 * @throws Exception if escape function is not callable
 */
public static function replacePlaceholders($stmt, array $params = [], $escape = 'addslashes'):string
{
	// Variable to store where place placeholder replacement was
	$offset = 0;

	// Check escape function
	if (!is_callable($escape))
	{
		throw new Exception('Escape function is not callable');
	}

	// Replace each of the placeholders
	while ($pos = strpos($stmt, '?', $offset))
	{
		// Figure out the replacement string for statement
		$value = array_shift($params);
		if (is_int($value) || is_float($value))
		{
			$replacement = $value;
		}
		elseif (is_null($value))
		{
			$replacement = 'NULL';
		}
		else
		{
			$value = call_user_func($escape, $value);
			$replacement = "'{$value}'";
		}

		// Replace placeholder with replacement string
		$stmt = substr_replace($stmt, $replacement, $pos, 1);
		$offset = $pos;
	}

	// Return statement
	return $stmt;
}

/*=======================================================*/
}
