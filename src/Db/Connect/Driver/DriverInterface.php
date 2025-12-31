<?php declare(strict_types = 1);

namespace Yau\Db\Connect\Driver;

/**
 * Interface for classes used for connecting to databases
 */
interface DriverInterface
{
/*=======================================================*/

/**
 * Connect to a database and return the database handler object/resource
 *
 * @param array $params associative array containing the information for
 *                      connecting to the database
 * @return mixed either the object or resource for the database connection
 */
public static function connect($params);

/*=======================================================*/
}
