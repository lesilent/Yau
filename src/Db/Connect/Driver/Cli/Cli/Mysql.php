<?php declare(strict_types = 1);

namespace Yau\Db\Connect\Driver\Cli\Cli;

use Yau\Db\Connect\Driver\DriverInterface;

/**
* Class for returning a command for connecting to a MySQL database
*
* @author John Yau
* @link https://dev.mysql.com/doc/refman/8.0/en/mysql.html
*/
class Mysql implements DriverInterface
{
/*=======================================================*/

/**
 * Default mysql command
 *
 * @var string
 */
protected static $COMMAND = 'mysql';

/**
 * Return a command for connecting to a MySQL database using parameters
 *
 * @param array $params associative array containing the information for
 *                      connecting to the database
 * @return string the command used to connect to the database
 * @link https://dev.mysql.com/doc/refman/8.0/en/mysql-command-options.html
 */
public static function connect($params)
{
	// Build command
	$cmd = self::$COMMAND
		. (isset($params['host']) ? ' --host=' . escapeshellarg($params['host']) : '')
		. (!empty($params['port']) ? ' --port=' . escapeshellarg($params['port']) : '')
		. (isset($params['dbname'])   ? ' --database=' . escapeshellarg($params['dbname']) : '')
		. (isset($params['username']) ? ' --user=' . escapeshellarg($params['username'])   : '')
		. ((isset($params['password']) && is_string($params['password']))
			? ' --password=' . escapeshellarg($params['password'])
			: '');

	// Return command
	return $cmd;
}

/*=======================================================*/
}
