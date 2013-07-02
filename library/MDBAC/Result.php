<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_MDBAC
*/

namespace Yau\MDBAC;

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
* @author   John Yau
* @category Yau
* @package  Yau_MDBAC
*/
class Result extends \ArrayIterator
{
/*=======================================================*/

/**
* Constructor
*
* @param array $result array of associative arrays of connection info
*                       returned by Util_DB_Config::query() function
*/
public function __construct($result)
{
	parent::__construct($result);
}

/**
* Fetch a single result from result set
*
* @return array  a single associative array of database connection info, or
*                NULL when there is no more results
*/
public function fetch()
{
	if ($row = $this->current())
	{
		$this->next();
		return $row;
	}
	return NULL;
}

/*=======================================================*/
}