<?php
namespace Manychois\Wpx\Tests;

use Manychois\Wpx\Utility;
use Manychois\Wpx\Tests\WpContext;
use Manychois\Wpx\NavLink;

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

	public function test_getPaginatedPostLinks_args_number()
	{
		$wp = $this->wp();
		$wp->method('wp_link_pages')->willReturn(' <a href="http://default.localhost.com/template-paginated/"><span>1</span></a> <span>2</span> <a href="http://default.localhost.com/template-paginated/3/"><span>3</span></a>');
		$u = new Utility($wp);
		$links = $u->getPaginatedPostLinks();
		$this->assertSame(3, count($links));
		$this->assertSame('[PAGE][1][http://default.localhost.com/template-paginated/]', $this->strNavLink($links[0]));
		$this->assertSame('[CURRENT][2][]', $this->strNavLink($links[1]));
		$this->assertSame('[PAGE][3][http://default.localhost.com/template-paginated/3/]', $this->strNavLink($links[2]));
	}

	public function test_getPaginatedPostLinks_args_next()
	{
		$wp = $this->wp();
		$wp->method('wp_link_pages')->willReturn('<a href="http://default.localhost.com/template-paginated/"><span>PREV</span></a> <a href="http://default.localhost.com/template-paginated/3/"><span>NEXT</span></a>');
		$u = new Utility($wp);
		$links = $u->getPaginatedPostLinks([
			'nextpagelink' => 'Next Page',
			'previouspagelink' => 'Previous Page'
		]);
		$this->assertSame(2, count($links));
		$this->assertSame('[PREV][Previous Page][http://default.localhost.com/template-paginated/]', $this->strNavLink($links[0]));
		$this->assertSame('[NEXT][Next Page][http://default.localhost.com/template-paginated/3/]', $this->strNavLink($links[1]));
	}

	public function test_getPostPaginationLinks()
	{
		$wp = $this->wp();
		$wp->method('paginate_links')->willReturn("<a class=\"prev page-numbers\" href=\"http://default.localhost.com/page/2/\">PREV</a>
<a class='page-numbers' href='http://default.localhost.com/'>1</a>
<a class='page-numbers' href='http://default.localhost.com/page/2/'>2</a>
<span aria-current='page' class='page-numbers current'>3</span>
<a class='page-numbers' href='http://default.localhost.com/page/4/'>4</a>
<a class='page-numbers' href='http://default.localhost.com/page/5/'>5</a>
<span class=\"page-numbers dots\">&hellip;</span>
<a class='page-numbers' href='http://default.localhost.com/page/8/'>8</a>
<a class=\"next page-numbers\" href=\"http://default.localhost.com/page/4/\">NEXT</a>");

		$u = new Utility($wp);
		$links = $u->getPostPaginationLinks([
			'prev_text' => 'Previous',
			'next_text' => 'Next'
		]);
		$this->assertSame(9, count($links));
		$this->assertSame('[PREV][Previous][http://default.localhost.com/page/2/]', $this->strNavLink($links[0]));
		$this->assertSame('[PAGE][1][http://default.localhost.com/]', $this->strNavLink($links[1]));
		$this->assertSame('[PAGE][2][http://default.localhost.com/page/2/]', $this->strNavLink($links[2]));
		$this->assertSame('[CURRENT][3][]', $this->strNavLink($links[3]));
		$this->assertSame('[PAGE][4][http://default.localhost.com/page/4/]', $this->strNavLink($links[4]));
		$this->assertSame('[PAGE][5][http://default.localhost.com/page/5/]', $this->strNavLink($links[5]));
		$this->assertSame('[ELLIPSIS][][]', $this->strNavLink($links[6]));
		$this->assertSame('[PAGE][8][http://default.localhost.com/page/8/]', $this->strNavLink($links[7]));
		$this->assertSame('[NEXT][Next][http://default.localhost.com/page/4/]', $this->strNavLink($links[8]));
	}

	private function strNavLink(NavLink $link)
	{
		$t = '';
		switch ($link->type) {
			case NavLink::PAGE:
				$t = 'PAGE';
				break;
			case NavLink::PREV:
				$t = 'PREV';
				break;
			case NavLink::NEXT:
				$t = 'NEXT';
				break;
			case NavLink::CURRENT:
				$t = 'CURRENT';
				break;
			case NavLink::ELLIPSIS:
				$t = 'ELLIPSIS';
				break;
		}
		return sprintf('[%s][%s][%s]', $t, $link->text, $link->href);
	}
}