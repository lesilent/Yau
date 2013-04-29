<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
*/

namespace Yau\Db\Connect\Driver\Cli\Cli;

use Yau\Db\Connect\Driver\DriverInterface;

/**
* Class for returning a command for connecting to DB2 database via ODBC
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
*/
class Db2 implements DriverInterface
{
/*=======================================================*/

/**
* Return a command for connecting to a DB2 database using parameters
*
* @param  array   $params associative array containing the information for
*                         connecting to the database
* @return string  the command used to connect to the database
*/
public static function connect($params)
{
	// Form SQL connect command
	$sql = 'CONNECT'
	     . (isset($params['dbname']) ? ' TO ' . $params['dbname']   : '')
	     . (isset($params['username']) ? ' USER ' . $params['username'] : '')
	     . ((isset($params['password']) && is_string($params['password']))
	     	? ' USING ' . $params['password'] : '');

	// Form command string
	$cmd = 'db2 ' . escapeshellarg($sql) . ' && db2 && db2 TERMINATE';

	// Return command
	return $cmd;
}

/*=======================================================*/
}
