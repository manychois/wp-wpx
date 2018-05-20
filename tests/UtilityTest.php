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

	public function test_minimizeHead()
	{
		$wp = new WpContext();
		$logs = [];
		$wp->addHook('add_filter', function($tag, $function_to_add) use (&$logs) {
			$logs[] = "add_filter:$tag:$function_to_add";
		});
		$wp->addHook('remove_action', function($tag, $function_to_remove) use (&$logs) {
			$logs[] = "remove_action:$tag:$function_to_remove";
		});
		$u = new Utility($wp);
		$u->minimizeHead();
		$expected = [
			'remove_action:wp_head:rest_output_link_wp_head',
			'remove_action:wp_head:print_emoji_detection_script',
			'remove_action:wp_print_styles:print_emoji_styles',
			'add_filter:emoji_svg_url:__return_false',
			'remove_action:wp_head:feed_links_extra',
			'remove_action:wp_head:wp_generator',
			'add_filter:the_generator:__return_false',
			'remove_action:wp_head:rsd_link',
			'remove_action:wp_head:wp_shortlink_wp_head',
			'remove_action:wp_head:wlwmanifest_link',
			'remove_action:wp_head:wp_oembed_add_discovery_links',
			'remove_action:wp_head:wp_oembed_add_host_js'
		];
		$this->assertSame(implode("\n", $expected), implode("\n", $logs));

		$logs = [];
		$u->minimizeHead([
			'admin_bar' => true,
			'api' => false,
			'canonical' => true,
			'emoji' => false,
			'extra_feed_links' => false,
			'generator' => false,
			'prev_next' => true,
			'res_hint' => true,
			'rsd' => false,
			'shortlink' => false,
			'wlw' => false,
			'wp_oembed' => false
		]);
		$expected = [
			'add_filter:show_admin_bar:__return_false',
			'remove_action:wp_head:rel_canonical',
			'remove_action:wp_head:adjacent_posts_rel_link_wp_head',
			'remove_action:wp_head:wp_resource_hints'
		];
		$this->assertSame(implode("\n", $expected), implode("\n", $logs));
	}
}