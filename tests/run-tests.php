#!/usr/local/bin/php
<?php

/**
*
* @author John Yau
*/
namespace YauTest;

// Define path to library
$libpath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'library';

// Load main Yau class and register autoloader
require $libpath . DIRECTORY_SEPARATOR . 'Yau.php';
\Yau\Yau::registerAutoloader();

// Load PHPUnit
require 'phpunit.phar';


