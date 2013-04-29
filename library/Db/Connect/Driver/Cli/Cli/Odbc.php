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
use Yau\Db\Connect\Driver\Cli\Cli\Db2;

/**
* Class for returning a command for connecting to an ODBC database
*
* Note: currently only DB2 is supported
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
*/
class Odbc implements DriverInterface
{
/*=======================================================*/

/**
* Return a command for connecting to a ODBC database using parameters
*
* @param  array   $params associative array containing the information for
*                         connecting to the database
* @return string  the command used to connect to the database
* @uses   Yau\Db\Connect\Cli\Db2::connect()
*/
public static function connect($params)
{
	return Db2::connect($params);
}

/*=======================================================*/
}
