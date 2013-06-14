<?php

/**
* Yau Tools
*
* Note: this file is used to register the auto loader
*
* @author   John Yau
* @category Yau
* @package  Yau
*/

if (!class_exists('Yau\\Yau', FALSE))
{
	require __DIR__ . DIRECTORY_SEPARATOR . 'Yau.php';
	\Yau\Yau::registerAutoloader();
}