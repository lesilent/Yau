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
 * Autoloader function
 *
 * @param string $class
 */
private static function autoload($class):void
{
	$ns_len = strlen(__NAMESPACE__);
	if (strcmp(substr($class, 0, $ns_len + 1), __NAMESPACE__ . '\\') == 0)
	{
		require_once __DIR__ . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, substr($class, $ns_len)) . '.php';
	}
}

/**
 * Register autoload function
 *
 * @return bool
 */
public static function registerAutoloader():bool
{
	return spl_autoload_register([__CLASS__, 'autoload']);
}

/**
 * Unregister the autoload function using spl_autoload_unregister
 *
 * @return bool
 */
public static function unregisterAutoloader():bool
{
	return spl_autoload_unregister([__CLASS__, 'autoload']);
}

/*=======================================================*/
}
