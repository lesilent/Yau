<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_Mutex
*/

namespace Yau\Mutex;

/**
* Mutex class
*
* @author   John Yau
* @category Yau
* @package  Yau_Mutex
*/
class Mutex
{
/*=======================================================*/

/**
*
* @param  string $type     the type of mutex
* @param  mixed  $resource the resource
* @param  array  $options  associative array of options
* @return object
*/
public static function factory($type, $resource, $options = array())
{
	$class_name = __NAMESPACE__ . '\\Adapter\\' . ucfirst($type);
	return new $class_name($resource, $options);
}

/*=======================================================*/
}
