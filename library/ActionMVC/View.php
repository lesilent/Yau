<?php

/**
* Yau Tools
*
* @author   John Yau
* @category Yau
* @package  Yau_ActionMVC
*/
namespace Yau\ActionMVC;

use Yau\Savant\Savant;

/**
*
* @author   John Yau
* @category Yau
* @package  Yau_ActionMVC
*/
class View extends Savant
{
/*=================================================================*/

/**
* Base path to templates
*
* @var string
*/
protected $path;

/**
* Return the path to the templates
*
* @return string
*/
public function getBasePath()
{
	// Initialize path if not set
	if (!isset($this->path))
	{
		$this->path = (isset($_SERVER['SCRIPT_FILENAME']))
			? dirname($_SERVER['SCRIPT_FILENAME'])
			: '.';
	}

	// Return path
	return $this->path;
}

/**
* Set the path to templates
*
* @param string $path
*/
public function setBasePath($path)
{
	$this->path = realpath($path);
}

/**
* Render and return template as a string
*
* @param  $template string
* @return string
*/
public function render($template)
{
	$filename = $this->getBasePath() . DIRECTORY_SEPARATOR . $template . '.php';
	return $this->fetch($filename);
}

/*=================================================================*/
}
