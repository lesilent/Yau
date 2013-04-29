<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_MDBC
*/

namespace Yau\Db\Connect\Driver\Perl\Perl;

use Yau\Db\Connect\Driver\DriverInterface;
use Yau\Db\Exception\InvalidArgumentException;

/**
* Class for connecting to a database using Perl DBI
*
* @author     John Yau
* @category   Yau
* @package    Yau_MDBC
* @link       http://search.cpan.org/~timb/DBI/DBI.pm
*/
class Dbi implements DriverInterface
{
/*=======================================================*/

/**
* Connect to a MySQL database using parameters and return a PDO object
*
* Note: currently, only mysql driver is supported
*
* @param  array $params associative array containing the information for
*                       connecting to the database
* @return array an array of arguments for DBI's connection function
* @link   http://search.cpan.org/~timb/DBI/DBI.pm#connect
*/
public static function connect($params)
{
	// Define scheme (currently only DBI supported)
	static $scheme = 'DBI';

	// Check driver
	if (empty($params['driver']))
	{
		throw new InvalidArgumentException('Driver not defined');
	}
	if ($params['driver'] != 'mysql')
	{
		throw new InvalidArgumentException('Only mysql driver currently supported');
	}
	$driver = $params['driver'];

	// Convert parameters to key value pairs
	$pairs = array();
	foreach (array(
		'dbname' => 'database',
		'host'   => 'host',
		'port'   => 'port',
		) as $field => $name)
	{
		if (isset($params[$field]))
		{
			$pairs[] = $name . '=' . rawurlencode($params[$field]);
		}
	}

	// Form DSN string
	$dsn = $scheme . ':' . $params['driver']
	     . (empty($pairs) ? '' : ':' . implode(';', $pairs));

	// Return DBI connect arguments
	$args = array($dsn);
	if (isset($params['username']))
	{
		$args[] = $params['username'];
		if (isset($params['password']))
		{
			$args[] = $params['password'];
		}
	}

	// Return arguments;
	return $args;
}

/*=======================================================*/
}
