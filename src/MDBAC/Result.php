<?php declare(strict_types = 1);

namespace Yau\MDBAC;

use ArrayIterator;

/**
 * A MDBAC Config result set class
 *
 * Example
 * <code>
 * $config = new Yau\MDBAC\Config('db.conf.xml');
 * $result = $config->query('topbucks');
 * while ($db = $result->fetch())
 * {
 *     print_r($db);
 * }
 * </code>
 *
 * @author John Yau
 */
class Result extends ArrayIterator
{
/*=======================================================*/

/**
 * Constructor
 *
 * @param array $result array of associative arrays of connection info
 *                      returned by Yau\MDBAC\Config::query() function
 */
public function __construct($result)
{
	parent::__construct($result);
}

/**
 * Fetch a single result from result set
 *
 * @return array a single associative array of database connection info, or
 *               null when there is no more results
 */
public function fetch()
{
	if ($row = $this->current())
	{
		$this->next();
		return $row;
	}
	return null;
}

/*=======================================================*/
}