<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
*/

namespace Yau\Db\Connect\Driver\Python\Python;

use Yau\Db\Connect\Driver\DriverInterface;

/**
* Class for connecting to a database using Python's _mysql module
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
* @link     http://mysql-python.sourceforge.net/MySQLdb.html
*/
class Mysql implements DriverInterface
{
/*=======================================================*/

/**
* Return arguments for connecting to MySQL in Python
*
* @param  array $params associative array containing the information for
*                       connecting to the database
* @return array an associative array of arguments for _mysql connect function
* @link   http://mysql-python.sourceforge.net/MySQLdb.html
*/
public static function connect($params)
{
	// Process parameters
	$args = array();
	foreach (array(
		'driver'   => 'driver',
		'host'     => 'host',
		'username' => 'user',
		'password' => 'passwd',
		'dbname'   => 'db',
		'port'     => 'port',
		) as $field => $name)
	{
		if (isset($params[$n]))
		{
			$args[$n] = $params[$n];
		}
	}

	// Return arguments;
	return $args;
}

/*=======================================================*/
}
