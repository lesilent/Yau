<?php declare(strict_types = 1);

namespace Yau;

/**
 * Yau Tools
 *
 * Note: this file is used to register the auto loader
 *
 * Load the file to register autoloader
 * <code>
 * require 'Yau/autoload.php';
 * </code>
 *
 * @author John Yau
 */
if (!class_exists('Yau\Yau', false))
{
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'Yau.php';
	Yau::registerAutoloader();
}
