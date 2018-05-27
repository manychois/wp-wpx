<?php
namespace Manychois\Wpx\Tests;

use Manychois\Wpx\Utility;
use Manychois\Wpx\Tests\WpContext;

class UtilityTest extends UnitTestCase
{
	/**
	 * @dataProvider data_findAspectRatio
	 */
	public function test_findAspectRatio($expected, $w, $h)
	{
		$u = new Utility($this->wp());
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
		$wp = $this->wp();
		$u = new Utility($wp);
		$actual = $u->getFromGet('abc');
		$this->assertNull($actual);

		$actual = $u->getFromGet('abc', '123');
		$this->assertSame('123', $actual);

		$_GET['abc'] = "It\'s fun!";
		$wp->method('stripslashes_deep')->willReturn("It's fun!");

		$actual = $u->getFromGet('abc', '123');
		$this->assertSame("It's fun!", $actual);
	}

	public function test_getFromPost()
	{
		$wp = $this->wp();
		$u = new Utility($wp);
		$actual = $u->getFromPost('abc');
		$this->assertNull($actual);

		$actual = $u->getFromPost('abc', '123');
		$this->assertSame('123', $actual);

		$_POST['abc'] = "It\'s fun!";
		$wp->method('stripslashes_deep')->willReturn("It's fun!");

		$actual = $u->getFromPost('abc', '123');
		$this->assertSame("It's fun!", $actual);
	}

	public function test_registerScript()
	{
		$u = new Utility($this->wp());
		$tag = "<script type='text/javascript' src='http://localhost/sample/wp-content/themes/twentyseventeen/assets/js/global.js?ver=1.0'></script>\n";
		$handle = 'twentyseventeen-global';
		$src = 'http://localhost/sample/wp-content/themes/twentyseventeen/assets/js/global.js?ver=1.0';

		$u->registerScript('twentyseventeen-global', array('src' => 'http://localhost/sample/wp-content/themes/twentyseventeen/assets/js/global.js'));
		$actual = $u->script_loader_tag($tag, $handle, $src);
		$expected = '<script src="http://localhost/sample/wp-content/themes/twentyseventeen/assets/js/global.js"></script>' . "\n";
		$this->assertSame($expected, $actual);
	}

	public function test_registerStyle()
	{
		$u = new Utility($this->wp());
		$html = "<link rel='stylesheet' id='twentyseventeen-style-css'  href='http://localhost/sample/wp-content/themes/twentyseventeen/style.css?ver=4.9.6' type='text/css' media='all' />\n";
		$handle = 'twentyseventeen-style';
		$href = 'http://localhost/sample/wp-content/themes/twentyseventeen/style.css?ver=4.9.6';

		$u->registerStyle('twentyseventeen-style', array('href' => 'http://localhost/sample/wp-content/themes/twentyseventeen/style.css'));
		$actual = $u->style_loader_tag($html, $handle, $href);
		$expected = '<link rel="stylesheet" href="http://localhost/sample/wp-content/themes/twentyseventeen/style.css" />' . "\n";
		$this->assertSame($expected, $actual);
	}
}