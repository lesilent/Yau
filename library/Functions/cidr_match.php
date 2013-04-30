<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_Functions
*/

namespace Yau\Functions;

/**
* Function for returning whether an ip matches a CIDR notation mask
*
* Example
* <code>
* use Yau\Functions\Functions;
*
* // Using arrays
* echo Functions::cidr_match('192.168.1.23', '192.168.1.0/24') ? 'matches' : 'no';
* </code>
*
* @param  string  $ip   the ip address
* @param  string  $mask the CIDR mask
* @return boolean TRUE if ip address matches mask, FALSE otherwise
* @link   http://stackoverflow.com/questions/594112
*/
function cidr_match($ip, $mask)
{
	list($subnet, $bits) = explode('/', $mask);
	$ip = ip2long($ip);
	$subnet = ip2long($subnet);
	$mask = -1 << (32 - $bits);
	$subnet &= $mask; // nb: in case the supplied subnet wasn't correctly aligned
	return ($ip & $mask) == $subnet;
}
