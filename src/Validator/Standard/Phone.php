<?php declare(strict_types = 1);

namespace Yau\Validator\Standard;

use Yau\Validator\ValidatorInterface;

/**
 * Class to check that a value is a valid north american phone number format
 *
 * @author John Yau
 */
class Phone implements ValidatorInterface
{
/*=======================================================*/

/**
 * Regular expression for validating phone number
 *
 * @var string
 */
const PATTERN = '/^1?(([2-9])(\d)(\d))(([2-9])(\d)(\d))((\d)(\d)(\d)(\d))$/';

/**
 * Array of NPA ids
 *
 * @var array
 */
private static $NPAS = [];

/**
 * Return the path to NPA data file
 *
 * @return string
 * @link https://www.nanpa.com/reports/npa-reports
 * @link https://reports.nanpa.com/public/npa_report.csv
 */
private function getNpaReportsFile(): string
{
	return dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'npa_report.csv';
}

/**
* Return an associative array of info on one or all area codes
*
* @param string|null $code
* @return array|false
*/
public function getDatabaseInfo(?string $code = null)
{
	if (empty(self::$NPAS))
	{
		$fields = [];
		$fields_count = 0;
		$filename = $this->getNpaReportsFile();
		$handle = fopen($filename, 'r');
		flock($handle, LOCK_SH);
		while ($row = fgetcsv($handle, null, ',', '"', ''))
		{
			// Skip empty rows and file date row
			$row_count = count($row);
			if (empty($row[0]) || $row_count < 10)
			{
				continue;
			}

			if (empty($fields))
			{
				$fields = array_map('strtolower', $row);
				$fields_count = $row_count;
				continue;
			}
			if (!preg_match('/^\d{3}$/', $row[0]))
			{
				// Exclude possible non-numeric area codes
				continue;
			}

			// Handle lines that are shorter than the number of fields (not sure why that is)
			while ($row_count < $fields_count)
			{
				$row[] = '';
				$row_count++;
			}

			// Store row
			if ($row_count == $fields_count)
			{
				$npa_id = $row[0];
				$row = array_combine($fields, $row);

				// Some locations and countries match, but vary only
				// in ampersands vs the word "and". So we make them
				// the same so that code can check for match.
				foreach (['country', 'location'] as $field)
				{
					$row[$field] = str_replace('&', 'AND', $row[$field]);
				}

				// Store row
				self::$NPAS[$npa_id] = $row;
			}
		}
		flock($handle, LOCK_UN);
		fclose($handle);
	}
	return isset($code) ? (self::$NPAS[$code] ?? false) : self::$NPAS;
}

/**
 * Check that a value is a valid US zip code
 *
 * @param string $value the value to check
 * @return bool true if check passes, or false if not
 */
public function isValid($value): bool
{
	// Perform non-database checks
	if (!preg_match(self::PATTERN, preg_replace('/\D+/', '', $value), $matches)
		// https://en.wikipedia.org/wiki/Feature_group
		|| $matches[1] == '950'
		// https://en.wikipedia.org/wiki/Premium-rate_telephone_number
		// https://www.nanpa.com/numbering/9yy-nxx-codes
		|| $matches[1] == '900'
		// https://en.wikipedia.org/wiki/Area_code_700
		|| $matches[1] == '700'
		// https://en.wikipedia.org/wiki/N11_code
		|| ($matches[3] == '1' && $matches[4] == '1')
		// https://en.wikipedia.org/wiki/Toll-free_telephone_numbers_in_the_North_American_Numbering_Plan
		|| ($matches[2] == '8' && ($matches[3] == '8' || ($matches[3] == $matches[4] && $matches[3] < '9')))
		// https://en.wikipedia.org/wiki/North_American_Numbering_Plan_expansion
		|| $matches[3] == '9'
		// Reserved by INC
		|| ($matches[2] == '3' && $matches[3] == '7')
		|| ($matches[2] == '9' && $matches[3] == '6')
		// Directory assistance
		|| $matches[1] == '555'
		|| ($matches[5] == '555'
			&& (($matches[10] == '0' && $matches[11] == '1')  // Fictious numbers
			|| $matches[9] == '1212'    // National user directory assistance
			|| $matches[9] == '4334'))  // National assigned use
		// Canadian Non-Geographic services
		|| ($matches[2] == '6' && $matches[3] == $matches[4] && $matches[3] != '1' && $matches[3] != '6' && $matches[3] < '9')
	)
	{
		return false;
	}

	// Perform checks that involve the database
	$info = $this->getDatabaseInfo($matches[1]);
	if (empty($info)
		|| strcasecmp($info['assignable'], 'No') == 0
		|| strcasecmp($info['in_service'], 'N') == 0)
	{
		return false;
	}

	// Return true
	return true;
}

/*=======================================================*/
}
