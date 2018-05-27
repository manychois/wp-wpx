<?php
namespace Manychois\Wpx\Tests\Integration;

use Manychois\Wpx\Utility;

class UtilityTest extends IntegrationTestCase
{
	private $minimizeHeadArgs;

	public function setUp()
	{
		parent::setUp();
		$this->minimizeHeadArgs = [
			'admin_bar' => false,
			'api' => false,
			'canonical' => false,
			'emoji' => false,
			'extra_feed_links' => false,
			'generator' => false,
			'prev_next' => false,
			'res_hint' => false,
			'rsd' => false,
			'shortlink' => false,
			'wlw' => false,
			'wp_oembed' => false
		];
	}

	/**
	 * This is the controlled experiment to ensure certain outputs exist before minimizeHead() is called.
	 */
	public function test_minimizeHead_nothing()
	{
		// <link rel='https://api.w.org/' href='http://example.org/index.php?rest_route=/' />
		ob_start();
		wp_head();
		$headOutput = ob_get_clean();
		// For test_minimizeHead_api()
		$this->assertTrue(strpos($headOutput, "<link rel='https://api.w.org/' href='http://example.org/index.php?rest_route=/' />") !== false);
		// For test_minimizeHead_emoji()
		$this->assertTrue(strpos($headOutput, "<link rel='dns-prefetch' href='//s.w.org' />") !== false);
		$this->assertTrue(strpos($headOutput, "window._wpemojiSettings") !== false);
		$this->assertTrue(strpos($headOutput, "img.wp-smiley") !== false);
		// For test)minimizeHead_generator()
		$this->assertTrue(strpos($headOutput, '<meta name="generator" content="WordPress') !== false);
		// For test_minimizeHead_rsd()
		$this->assertTrue(strpos($headOutput, '<link rel="EditURI" type="application/rsd+xml" title="RSD" href="http://example.org/xmlrpc.php?rsd" />') !== false);
		// For test_minimizeHead_wlw()
		$this->assertTrue(strpos($headOutput, '<link rel="wlwmanifest" type="application/wlwmanifest+xml" href="http://example.org/wp-includes/wlwmanifest.xml" />') !== false);
	}

	public function test_minimizeHead_api()
	{
		$args = $this->minimizeHeadArgs;
		$args['api'] = true;
		$u = new Utility($this->wp());
		$u->minimizeHead($args);
		ob_start();
		wp_head();
		$headOutput = ob_get_clean();
		$this->assertFalse(strpos($headOutput, "<link rel='https://api.w.org/' href='http://example.org/index.php?rest_route=/' />"), $headOutput);
	}

	public function test_minimizeHead_emoji()
	{
		$args = $this->minimizeHeadArgs;
		$args['emoji'] = true;
		$u = new Utility($this->wp());
		$u->minimizeHead($args);
		ob_start();
		wp_head();
		$headOutput = ob_get_clean();
		$this->assertFalse(strpos($headOutput, "<link rel='dns-prefetch' href='//s.w.org' />"));
		$this->assertFalse(strpos($headOutput, "window._wpemojiSettings"));
		$this->assertFalse(strpos($headOutput, "img.wp-smiley"));
	}

	public function test_minimizeHead_generator()
	{
		$args = $this->minimizeHeadArgs;
		$args['generator'] = true;
		$u = new Utility($this->wp());
		$u->minimizeHead($args);
		ob_start();
		wp_head();
		$headOutput = ob_get_clean();
		$this->assertFalse(strpos($headOutput, '<meta name="generator" content="WordPress'), $headOutput);
	}

	public function test_minimizeHead_rsd()
	{
		$args = $this->minimizeHeadArgs;
		$args['rsd'] = true;
		$u = new Utility($this->wp());
		$u->minimizeHead($args);
		ob_start();
		wp_head();
		$headOutput = ob_get_clean();
		$this->assertFalse(strpos($headOutput, '<link rel="EditURI" type="application/rsd+xml" title="RSD" href="http://example.org/xmlrpc.php?rsd" />'), $headOutput);
	}

	public function test_minimizeHead_wlw()
	{
		$args = $this->minimizeHeadArgs;
		$args['wlw'] = true;
		$u = new Utility($this->wp());
		$u->minimizeHead($args);
		ob_start();
		wp_head();
		$headOutput = ob_get_clean();
		$this->assertFalse(strpos($headOutput, '<link rel="wlwmanifest" type="application/wlwmanifest+xml" href="http://example.org/wp-includes/wlwmanifest.xml" />'), $headOutput);
	}
}