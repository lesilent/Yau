<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_ClassLoader
*/

namespace Yau\ClassLoader;

/**
* Class for autoloading classes
*
* @author   John Yau
* @category Yau
* @package  Yau_ClassLoader
*/
class ClassLoader
{
/*=======================================================*/

/**
* Map of namespace prefixes to path
*
* @var array
*/
protected $namespaces = array();

/**
* Map of class prefixes to path
*
* @var array
*/
protected $prefixes = array();

/**
* Flag for whether autoloaded has been registered or not
*
* @var boolean
*/
protected $registered = FALSE;

/**
* Return the path for a class based on registered namespaces and prefix
*
* @param  string $class_name the full class name
* @return mixed  the fully formed path if there's a match, or FALSE if not
*/
public function getPath($class_name)
{
	// Load classes with a namespace
	if (($ns_pos = strpos($class_name, '\\')) !== FALSE)
	{
		foreach ($this->namespaces as $ns => $path)
		{
			$ns_len = strlen($ns);
			$ns_str = substr($class_name, 0, $ns_len);
			if (strcmp($ns_str . '\\', $ns . '\\') == 0)
			{
				$sub_path = substr($class_name, $ns_len + 1);
				if (($last_ns_pos = strrpos($sub_path, '\\')) === FALSE)
				{
					return $path . DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $sub_path) . '.php';
				}
				else
				{
					$class_name = substr($sub_path, $last_ns_pos + 1);
					$sub_path = substr($sub_path, 0, $last_ns_pos);
					return $path . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $sub_path) . DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $class_name). '.php';
				}
			}
		}
	}
	else
	{
		// Load classes with a prefix
		foreach ($this->prefixes as $prefix => $path)
		{
			$prefix_len = strlen($prefix);
			$prefix_str = substr($class_name, 0, $prefix_len);
			if (strcmp($prefix_str, $prefix) == 0)
			{
				return $path . DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, substr($class_name, $prefix_len)) . '.php';
			}
		}
	}

	// Return FALSE if no matches found
	return FALSE;
}

/**
* Return whether a class or interface file exists to be loaded
*
* @param  string  $class_name
* @return boolean
*/
public function classExists($class_name)
{
	return (class_exists($class_name, FALSE)
		|| (($path = $this->getPath($class_name)) && is_readable($path)));
}

/**
* Load a class
*
* @param  string  $class_name the name of the class to load
* @return boolean
*/
public function loadClass($class_name)
{
	if (class_exists($class_name, FALSE) || interface_exists($class_name, FALSE))
	{
		return TRUE;
	}

	// Load class
	if (($path = $this->getPath($class_name))
		&& include($path))
	{
		return TRUE;
	}
	return FALSE;
}

/**
* Alias for loadClass() method
*
* @param  string  $class_name the name of the class to load
* @return boolean
*/
public function load($class_name)
{
	return $this->loadClass($class_name);
}

/**
* Register a namespace prefix with a path
*
* @param string $namespace
* @param string $path
*/
public function registerNamespace($namespace, $path)
{
	$this->namespaces[$namespace] = rtrim($path, '\\');
	$this->register();
}

/**
* Register multiple namespaces
*
* @param array $namespaces
*/
public function registerNamespaces($namespaces)
{
	foreach ($namespaces as $ns => $path)
	{
		$this->registerNamespace($ns, $path);
	}
	$this->register();
}

/**
* Register prefix
*
* @param string $prefix
* @param string $path
*/
public function registerPrefix($prefix, $path)
{
	$this->prefixes[$prefix] = $path;
	$this->register();
}

/**
* Register multiple prefixes
*
* @param array $prefixes
*/
public function registerPrefixes($prefixes)
{
	foreach ($prefixes as $prefix => $path)
	{
		$this->registerPrefix($prefix, $path);
	}
	$this->register();
}

/**
* Register autoloader using spl_autoload_register
*
* Note: no need to call this method, since this is called automatically
* when a namespace or prefix is registered
*
* @see    spl_autoload_register()
* @return boolean
*/
public function register()
{
	if (!$this->registered && spl_autoload_register(array($this, 'loadClass')))
	{
		return $this->registered = TRUE;
	}
	return FALSE;
}

/**
* Unregister the autoload function using spl_autoload_unregister
*
* @see    spl_autoload_unregister()
* @return boolean
*/
public function unregister()
{
	if ($this->registered && spl_autoload_unregister(array($this, 'loadClass')))
	{
		$this->registered = FALSE;
		return TRUE;
	}
	return FALSE;
}

/**
* Destructor
*/
public function __destruct()
{
	$this->unregister();
}

/*=======================================================*/
}
