<?php declare(strict_types = 1);

namespace Yau\ActionMVC;

use Yau\Savant\Savant;
use Yau\ActionMVC\ObjectTrait;
use Yau\Singleton\SingletonTrait;

/**
 * Default view object for ActionMVC
 *
 * @author John Yau
 */
class View extends Savant
{
use ObjectTrait, SingletonTrait;
/*=================================================================*/

/**
 * Base path to templates
 *
 * @var string
 */
private $path;

/**
 * Return the path to the templates
 *
 * @return string
 */
public function getBasePath(): string
{
	// Initialize path if not set
	if (!isset($this->path))
	{
		$this->path = (empty($_SERVER['SCRIPT_FILENAME']))
			? '.' : dirname($_SERVER['SCRIPT_FILENAME']);
	}

	// Return path
	return $this->path;
}

/**
 * Set the path to templates
 *
 * @param string $path
 */
public function setBasePath($path): void
{
	$this->path = realpath($path);
}

/**
 * Render and return template as a string
 *
 * @param string $template
 * @return string
 */
public function render(?string $template = null): string
{
	$filename = $this->getBasePath() . DIRECTORY_SEPARATOR . $template . '.php';
	return $this->fetch($filename);
}

/*=================================================================*/
}
