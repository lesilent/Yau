<?php

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
* @author   John Yau
* @category Yau
* @package  Yau
*/

if (!class_exists('Yau\\Yau', FALSE))
{
	require __DIR__ . DIRECTORY_SEPARATOR . 'Yau.php';
}
if (!class_exists('Yau', FALSE))
{
	final class Yau extends Yau\Yau { }
}
Yau::registerAutoloader();

