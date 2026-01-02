<?php declare(strict_types = 1);

namespace Yau\Validator\Standard;

use Yau\Validator\ValidatorInterface;

/**
 * Class to check that a value is a valid email address
 *
 * @author John Yau
 */
class Email implements ValidatorInterface
{
/*=======================================================*/

/**
 * Regular expression for validating an email address format
 *
 * @var string
 * @link http://gmailblog.blogspot.com/2008/03/2-hidden-ways-to-get-more-from-your.html
 * @link http://en.wikipedia.org/wiki/List_of_Internet_top-level_domains
 */
const PATTERN = '/^\w[\w\.\-\+]*@(?:[a-z0-9][a-z0-9\-]*\.)+((?:xn\-\-)?[a-z0-9]{2,18})$/i';

/**
 * Array of TLDs
 *
 * @var array
 * @link http://data.iana.org/TLD/tlds-alpha-by-domain.txt
 */
private static $TLDS = [];

/**
 * Return the path to data file containing TLDs
 *
 * @return string
 * @link http://data.iana.org/TLD/tlds-alpha-by-domain.txt
 */
private function getTldDataFile(): string
{
	return dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'tlds-alpha-by-domain.txt';
}

/**
* Return an associative array of array TLDs
*
* @param bool $assoc
* @return array
*/
public function getTlds(bool $assoc = false): array
{
	if (empty(self::$TLDS))
	{
		$filename = $this->getTldDataFile();
		if (preg_match_all('/^[A-Z]+(?:\-\-[0-9A-Z]+)?/m', file_get_contents($filename), $matches))
		{
			foreach ($matches[0] as $tld)
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
 * @param mixed $value the value to check
 * @return bool true if check passes, or false if not
 */
public function isValid($value): bool
{
	return (filter_var($value, FILTER_VALIDATE_EMAIL)
		&& preg_match(self::PATTERN, $value, $matches)
		&& ($tlds = $this->getTlds(true))
		&& isset($tlds[strtolower($matches[1])]));
}

/*=======================================================*/
}
