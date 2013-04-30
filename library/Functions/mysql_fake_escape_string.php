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
* Escapes special characters in a string for use in an SQL statement
*
* This attempts to mimick the functionality of the mysqli_real_escape_string()
* function without the need for MySQL connection, and assuming default character
* set.
*
* Example
* <code>
* use Yau\Functions\Functions;
*
* $query = sprintf("SELECT * FROM users WHERE user='%s' AND password='%s'",
*          Functions::mysql_fake_escape_string($user),
*          Functions::mysql_fake_escape_string($password));
* </code>
*
* @param   string $unescaped_string
* @return  string
* @link    http://dev.mysql.com/doc/refman/5.6/en/string-literals.html
* @link    http://dev.mysql.com/doc/refman/5.6/en/mysql-real-escape-string.html
*/
function mysql_fake_escape_string($escapestr)
{
	return strtr($escapestr, array(
		"\x00" => '\0',
		"\n"   => '\n',
		"\r"   => '\r',
		'\\'   => '\\\\',
		'\''   => '\\\'',
		'"'    => '\\"',
		"\x1a" => '\\Z',
	));
}
