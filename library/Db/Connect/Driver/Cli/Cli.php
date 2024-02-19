<?php declare(strict_types = 1);

namespace Yau\Db\Connect\Driver\Cli;

use Yau\Db\Connect\Driver\DriverInterface;
use InvalidArgumentException;

/**
* Class for connecting to a database from the command line
*
* @author John Yau
*/
class Cli implements DriverInterface
{
/*=======================================================*/

/**
 * Connect to a database using parameters
 *
 * @param array $params associative array containing the information for
 *                      connecting to the database
 * @return string the command line for connecting to the database
 * @throws Exception if unable to connect to database successfully
 */
public static function connect($params)
{
	// Look for driver-specific subclass
	if (empty($params['driver']))
	{
		throw new InvalidArgumentException('No CLI driver specified for connection');
	}
	$class_name = __CLASS__ . '\\' . ucwords($params['driver']);

	// Call connect function in subclass
	return call_user_func(array($class_name, __FUNCTION__), $params);
}

/*=======================================================*/
}
