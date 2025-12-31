<?php declare(strict_types=1);

namespace Yau;

/**
 * Main Yau class
 *
 * <code>
 * use Yau\Yau;
 * require_once 'Yau/Yau.php';
 * Yau::registerAutoloader();
 * </code>
 *
 * @author John Yau
 */
class Yau
{
/*=======================================================*/

/**
* Flag for whether autoloader has been registered or not
*
* @var bool
*/
private static $registered = false;

/**
 * Autoloader function
 *
 * @param string $class
 */
private static function autoload($class): void
{
	$ns_len = strlen(__NAMESPACE__);
	if (strcmp(substr($class, 0, $ns_len + 1), __NAMESPACE__ . '\\') == 0)
	{
		require_once __DIR__ . str_replace('\\', DIRECTORY_SEPARATOR, substr($class, $ns_len)) . '.php';
	}
}

/**
 * Register autoload function
 *
 * @return bool
 */
public static function registerAutoloader(): bool
{
	return (self::$registered) ? true : (spl_autoload_register([__CLASS__, 'autoload']) && (self::$registered = true));
}

/**
 * Unregister the autoload function using spl_autoload_unregister
 *
 * @return bool
 */
public static function unregisterAutoloader(): bool
{
	return (self::$registered) ? (spl_autoload_unregister([__CLASS__, 'autoload']) && !(self::$registered = false)) : false;
}

/*=======================================================*/
}
