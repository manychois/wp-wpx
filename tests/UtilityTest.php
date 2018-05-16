<?php
namespace Manychois\Wpx\Tests;

use PHPUnit\Framework\TestCase;
use Manychois\Wpx\Utility;

class UtilityTest extends TestCase
{
	/**
	 * @dataProvider data_findAspectRatio
	 */
	public function test_findAspectRatio($expected, $w, $h)
	{
		$u = new Utility();
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
		$u = new Utility();
		$actual = $u->getFromGet('abc');
		$this->assertNull($actual);
	}
}