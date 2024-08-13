<?php declare(strict_types=1);

namespace Yau\Savant;

use PHPUnit\Framework\TestCase;
use Yau\Savant\Savant;

/**
 * Tests for Yau\Validator\RecordValidator
 */
class SavantTest extends TestCase
{
/*=======================================================*/

/**
 */
public function testFetchFalse():void
{
	$tpl = new Savant();
	$html = $tpl->fetch();
	$this->assertFalse($html);
}

/**
 */
public function testFetchString():void
{
	$filename = __DIR__ . DIRECTORY_SEPARATOR . 'template1.php';
	$contents = file_get_contents($filename);

	$values = ['title'=>'Savant Test', 'name'=>'World'];
	$expected = preg_replace_callback('/<\?=\s*\$(\w+)[^>]+?>/', fn($matches) => $values[$matches[1]] ?? '', $contents);

	// Template passed through constructor
	$tpl = new Savant($filename);
	$html = $tpl->assign($values)->fetch();
	$this->assertIsString($html);
	$this->assertSame($expected, $html);
	$html = $tpl->__toString();
	$this->assertIsString($html);
	$this->assertSame($expected, $html);

	// Template passed through fetch()
	$tpl = new Savant();
	$html = $tpl->assign($values)->fetch($filename);
	$this->assertIsString($html);
	$this->assertSame($expected, $html);

	$tpl->name = $values['name'] = 'John';
	$expected = preg_replace_callback('/<\?=\s*\$(\w+)[^>]+?>/', fn($matches) => $values[$matches[1]] ?? '', $contents);
	$html2 = $tpl->fetch($filename);
	$this->assertSame($expected, $html2);
	$this->assertNotEquals($html, $html2);
}

/**
 */
public function testRegisterFunction()
{
	$filename = __DIR__ . DIRECTORY_SEPARATOR . 'template2.php';
	$contents = file_get_contents($filename);

	$key = random_bytes(32);
	$values = ['user_id'=>mt_rand(1000, 9999)];
	$funcs = ['myhash'=>fn($data) => hash_hmac('sha256', strval($data), $key)];
	$expected = preg_replace_callback('/<\?=\s*\$(\w+)(?:\->(\w+)\(\s*\$(\w+)\s*\))?[^>]+?>/', function ($matches) use($values, $funcs) {
		if (strcmp($matches[1], 'this') == 0)
		{
			return call_user_func($funcs[$matches[2]], $values[$matches[3]]);
		}
		elseif (empty($matches[2]))
		{
			return $values[$matches[1]] ?? '';
		}
	}, $contents);

	$tpl = new Savant();
	foreach ($funcs as $name => $callback)
	{
		$tpl->registerFunction($callback, $name);
	}
	$tpl->assign($values);
	$text = $tpl->fetch($filename);
	$this->assertIsString($text);
	$this->assertSame($expected, $text);
}

/*=======================================================*/
}
