<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_Db
*/

namespace Yau\Db\Connect\Driver;

/**
* Interface for classes used for connecting to databases
*
* @category Yau
* @package  Yau_Db
*/
interface DriverInterface
{
/*=======================================================*/

/**
* Connect to a database and return the database handler object/resource
*
* @param  array   $params associative array containing the information for
*                         connecting to the database
* @return mixed  either the object or resource for the database connection
* @throws Exception if unable to connect to database
*/
public static function connect($params);

/*=======================================================*/
}

