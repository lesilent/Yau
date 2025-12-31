<?php declare(strict_types = 1);

namespace Yau\Savant;

use Yau\AccessObject\AccessObject;
use InvalidArgumentException;

/**
 * A templating class based on PHP Savant
 *
 * <p>
 * This class is only loosely based on PHP Savant, so not all of the functions
 * are implemented. And those that are implemented may not work in exactly the
 * same manner. For example, PHP Savant allows adding search paths for
 * templates. This functionality was decided against to avoid cluttering up
 * the class, and the capability can also be done outside of the class.
 * </p>
 *
 * <p>
 * Templating is based off of PHP, so only valid PHP syntax can be used. The
 * class implements various PHP interfaces like Iterator and ArrayAccess, you
 * can assign and retrieve variables to the object as if it was an array or
 * object. You can even use foreach() function on the object itself.
 * </p>
 *
 * Example 1. assigning values and loading template
 * <code>
 * use Yau\Savant\Savant;
 *
 * $tpl = new Savant('index.tpl.php');
 *
 * // Assigning values via ArrayAccess interface
 * $tpl['title'] = 'My Title';
 *
 * // Assigning values via overloading
 * $tpl->name = 'John Doe';
 * $tpl->stats = array('age'=>21, 'sex'=>'M', 'location'=>'Tucson');
 *
 * // Assigning values by assign function (preferred)
 * $tpl->assign(array('height'=>'6 feet', 'weight'=>150));
 *
 * // Display the template
 * $tpl->display();
 * </code>
 *
 * The template index.tpl.php
 * <code>
 * <html>
 * <head><title><?php echo htmlentities($title); ?></title></head>
 * <body>
 * <?php echo htmlentities($name); ?> at
 * <?php echo htmlentities($height); ?> and <?php echo htmlentities($weight); ?> lbs
 * <br />
 * <table>
 * <?php foreach ($mylist as $stat => $val) { ?>
 * <tr><th><?php echo htmlentities($stat); ?>:</th><td><?php echo htmlentities($val); ?></td></tr>
 * <?php } ?>
 * </body>
 * </html>
 * </code>
 *
 * Example 2. fetching template output
 * <code>
 * use Yau\Savant\Savant;
 *
 * $tpl = new Savant();
 * $tpl['options'] = array('name'=>'Name', 'age'=>'Age', 'height'=>'Height');
 *
 * $tpl['selectname'] = 'field1';
 * $dropdown1 = $tpl->fetch('dropdown.tpl.php');

 * $tpl['selectname'] = 'field2';
 * $dropdown2 = $tpl->fetch('dropdown.tpl.php');
 *
 * echo "<div>$dropdown1</div><div>$dropdown2</div>";
 * </code>
 *
 * The template dropdown.tpl.php
 * <code>
 * <select name="<?php echo htmlentities($selectname); ?>">
 * <?php foreach ($options as $val => $text) { ?>
 * <option value="<?php echo htmlentities($val); ?>"><?php echo htmlentities($text); ?></option>
 * <?php } ?>
 * </code>
 *
 * <p>
 * With the "extract" option (which by default is TRUE), variables assigned to
 * the template are extracted into the scope of the template. When a variable
 * of the same name exists, it'll be skipped.
 * </p>
 *
 * <p>
 * Conversely, the "compact" allows access to all of the variables by stuffing
 * them into a single associative array variable.
 * </p>
 *
 * Example of template variables
 * <code>
 * <?php echo $foo; ?>         <-- displaying a simple variable (non array/object)
 * <?php echo $foo[4]; ?>      <-- display the 5th element of a zero-indexed array
 * <?php echo $foo['bar']; ?>  <-- display the "bar" key value of an array
 * <?php echo $foo[$bar]; ?>   <-- display variable key value of an array
 * <?php echo $foo->bar; ?>    <-- display the object property "bar"
 * <?php echo $foo->bar(); ?>  <-- display the return value of object method "bar"
 * </code>
 *
 * The original design included functionality for filters, but it was decided
 * that this functionality would be not be used as frequently.
 *
 * @author John Yau
 * @todo Add caching
 */
class Savant extends AccessObject
{
/*=======================================================*/

/**
 * The current template
 *
 * @var string
 */
private $template;

/**
 * Associative array of registered functions
 *
 * @var array
 */
private $funcs = [];

//-------------------------------------
// Main class functions

/**
 * Constructor
 *
 * Example
 * <code>
 * use Yau\Savant\Savant;
 * $tpl = new Savant('index.tpl.php');
 * echo $tpl;
 * </code>
 *
 * @param string $template the path to the template file to assign; this can
 *                         be absolute or relative to the include path
 */
public function __construct(?string $template = null)
{
	// Store template
	if (!empty($template))
	{
		$this->setTemplate($template);
	}
}

/**
 * Return the currently assigned template source file
 *
 * Example
 * <code>
 * use Yau\Savant\Savant;
 * $tpl = new Savant('templates/mypage.tpl.php');
 *
 * // Outputs "templates/mypage.tpl.php"
 * echo $savant->getTemplate();
 * </code>
 *
 * @return string the default file that's been assigned to the current
 *                template, or false if none is currently assigned
 */
public function getTemplate()
{
	return  $this->template ?? false;
}

/**
 * Set the current assigned template source
 *
 * In most cases, this function shouldn't needed to be called, since templates
 * are usually assigned in the constructor.
 *
 * Example
 * <code>
 * // Change template to index2.tpl.php
 * $tpl->setTemplate('index2.tpl.php');
 * </code>
 *
 * @param string $template the path file to assign to the current template
 */
public function setTemplate(?string $template)
{
	$this->template = $template;
}

/**
 * Parse template, and either output it or return it as a string
 * Note: This function uses output buffering in order to grab the output.
 *
 * @param string $template the template to include if other than one
 *                         assigned in the constructor
 * @return string|false the output from the compiled template, or false if no
 *                      template is defined
 */
private function parse(?string $template = null)
{
	// If no template is passed, then use assigned one
	if (empty($template))
	{
		if (empty($this->template))
		{
			return false;
		}
		$template = $this->template;
	}

	// Extract variables
	extract($this->toArray(), EXTR_SKIP);

	// Include template
	return include($template);
}

/**
 * Compile a template and return the output as a string
 *
 * Example
 * <code>
 * // Instantiate object
 * use Yau\Savant\Savant;
 *
 * $tpl = new Savant('index.tpl.php');
 *
 * // Assign some variables
 * $tpl->firstname = 'John';
 * $tpl->lastname = 'Doe';
 *
 * // Fetch template string
 * $html = $tpl->fetch();
 * </code>
 *
 * <p>
 * Note: This function uses output buffering in order to grab the output.
 * </p
 *
 * @param string $template the template to include if other than one assigned
 *                         in the constructor
 * @return string|false the output from the compiled template, or NULL if no
 *                      template is defined
 */
public function fetch(?string $template = null)
{
	ob_start();
	if ($this->parse($template) === false)
	{
		if (ob_get_level() > 0)
		{
			ob_end_clean();
		}
		return false;
	}
	else
	{
		return (ob_get_level() > 0) ? ob_get_clean() : false;
	}
}

/**
 * Compile and display a template
 *
 * Example
 * <code>
 * use Yau\Savant\Savant;
 *
 * // Template assigned via constructor
 * $tpl = new Savant('page.tpl.php');
 * $tpl->display();
 * </code>
 *
 * Example of fetch and display
 * <code>
 * use Yau\Savant\Savant;
 *
 * $tpl = new Savant('index.tpl.php');
 *
 * // The following two are equivalent
 * echo $tpl->fetch();
 * $tpl->display();
 * </code>
 *
 * @param string $template the template to include if other than one assigned
 *                         in the constructor
 */
public function display(?string $template = null)
{
	return $this->parse($template);
}

/**
 * Register a callable function
 *
 * @param mixed $callback callable function
 * @param string $name option name for function
 * @throws InvalidArgumentException if function isn't callable or name is invalid
 */
public function registerFunction($callback, $name = null): void
{
	// Check function
	if (!is_callable($callback))
	{
		throw new InvalidArgumentException('Function is not callable');
	}

	// Get name of function
	if (empty($name))
	{
		$name = (is_array($name)) ? end($callback) : $callback;
		$name = array_pop(explode('::', $name));
	}
	if (!preg_match('/^[a-z]\w*$/i', $name))
	{
		throw new InvalidArgumentException('Invalid function name ' . $name);
	}

	// Store function
	$this->funcs[$name] = $callback;
}

/**
 * Magic method for handling calls
 *
 * @param string $func
 * @param array $args
 * @return mixed
 * @throws InvalidArgumentException if invalid function
 */
public function __call($func, $args)
{
	// Check function name
	if (empty($this->funcs[$func]))
	{
		throw new InvalidArgumentException('No function ' . $func . ' registered');
	}

	// Call function
	return call_user_func_array($this->funcs[$func], $args);
}

/**
 * Magic method to display the template output
 *
 * Example
 * <code>
 * use Yau\Savant\Savant;
 *
 * $tpl = new Savant('mypage.tpl.php');
 *
 * echo $tpl;
 * </code>
 *
 * @return string the template output
 * @uses Savant::fetch()
 */
public function __toString(): string
{
	return (string) $this->fetch();
}

/*=======================================================*/
}
