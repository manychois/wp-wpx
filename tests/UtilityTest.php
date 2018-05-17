<?php
namespace Manychois\Wpx\Tests;

use PHPUnit\Framework\TestCase;
use Manychois\Wpx\Utility;
use Manychois\Wpx\Tests\WpContext;

class UtilityTest extends TestCase
{
	/**
	 * @dataProvider data_findAspectRatio
	 */
	public function test_findAspectRatio($expected, $w, $h)
	{
		$wp = new WpContext();
		$u = new Utility($wp);
		$actual = $u->findAspectRatio($w, $h);
		$this->assertSame($expected, $actual);
	}

	public function data_findAspectRatio()
	{
		return [
			['', 1, 0],
			['', 0, 1],
			['', 1, 3],
			['1x1', 9, 10],
			['', 116, 100],
			['4x3', 4, 3],
			['', 9, 6],
			['16x9', 16, 9],
			['', 18, 9],
			['21x9', 21, 9],
			['', 23, 9]
		];
	}

	public function test_getFromGet()
	{
		$wp = new WpContext();
		$u = new Utility($wp);
		$actual = $u->getFromGet('abc');
		$this->assertNull($actual);

		$actual = $u->getFromGet('abc', '123');
		$this->assertSame('123', $actual);

		$_GET['abc'] = "It\'s fun!";
		$wp->addHook('stripslashes_deep', function($value) {
			return "It's fun!";
		});
		$actual = $u->getFromGet('abc', '123');
		$this->assertSame("It's fun!", $actual);
	}

	public function test_getFromPost()
	{
		$wp = new WpContext();
		$u = new Utility($wp);
		$actual = $u->getFromPost('abc');
		$this->assertNull($actual);

		$actual = $u->getFromPost('abc', '123');
		$this->assertSame('123', $actual);

		$_POST['abc'] = "It\'s fun!";
		$wp->addHook('stripslashes_deep', function($value) {
			return "It's fun!";
		});
		$actual = $u->getFromPost('abc', '123');
		$this->assertSame("It's fun!", $actual);
	}
}