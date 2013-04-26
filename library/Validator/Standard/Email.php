<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_Validator
*/

namespace Yau\Validator\Standard;

use Yau\Singleton\Singleton;
use Yau\Validator\ValidatorInterface;

/**
* Class to check that a value is a valid email address
*
* @author   John Yau
* @category Yau
* @package  Yau_Validator
*/
class Email extends Singleton implements ValidatorInterface
{
/*=======================================================*/

/**
* Regular expression for validating an email address format
*
* @var  string
* @link http://gmailblog.blogspot.com/2008/03/2-hidden-ways-to-get-more-from-your.html
* @link http://en.wikipedia.org/wiki/List_of_Internet_top-level_domains
*/
const REGEX = '/^\w[\w\.\-\+]*@(?:[a-z0-9][a-z0-9\-]*\.)+((?:xn\-\-)?[a-z0-9]{2,18})$/i';

/**
* Array of TLDs
*
* @link http://data.iana.org/TLD/tlds-alpha-by-domain.txt
*/
protected static $TLDS = array();

/**
* Return the path to data file containing TLDs
*
* @link   http://data.iana.org/TLD/tlds-alpha-by-domain.txt
* @return string
*/
public static function getTldDataFile()
{
	return __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'tlds-alpha-by-domain.txt';
}

/**
* Return an associative array of array TLDs
*
* @param  string $assoc
* @return array
*/
public static function getTlds($assoc = FALSE)
{
	if (empty(self::$TLDS))
	{
		$filename = self::getTldDataFile();
		if (preg_match_all('/^[A-Z]+(?:\-\-[0-9A-Z]+)?/m', file_get_contents($filename), $match))
		{
			foreach ($match[0] as $tld)
			{
				$lc_tld = strtolower($tld);
				self::$TLDS[$lc_tld] = $lc_tld;
			}
		}
	}
	return ($assoc) ? self::$TLDS : array_values(self::$TLDS);
}

/**
* Check that a value is a valid email address
*
* @param  mixed   $value the value to check
* @return boolean TRUE if check passes, or FALSE if not
*/
public function isValid($value)
{
	return (filter_var($value, FILTER_VALIDATE_EMAIL)
		&& preg_match(self::REGEX, $value, $match)
		&& ($tlds = self::getTlds(TRUE))
		&& isset($tlds[strtolower($match[1])]));
}

/*=======================================================*/
}
