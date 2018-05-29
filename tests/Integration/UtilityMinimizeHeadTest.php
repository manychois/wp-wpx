<?php
namespace Manychois\Wpx\Tests\Integration;

use Manychois\Wpx\Utility;

class UtilityMinimizeHeadTest extends IntegrationTestCase
{
	private $minimizeHeadArgs;

	public function setUp()
	{
		$this->set_permalink_structure('/%postname%/');
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

		$this->factory->post->create(['post_title' => 'Post One', 'post_status' => 'publish', 'post_date' => '2010-01-01 00:00:00']);
		$this->factory->post->create(['post_title' => 'Post Two', 'post_status' => 'publish', 'post_date' => '2010-02-01 00:00:00']);
		$this->factory->post->create(['post_title' => 'Post Three', 'post_status' => 'publish', 'post_date' => '2010-03-01 00:00:00']);

		wp_enqueue_script('test-css', 'https://abc.def.com/default.css');
		$this->go_to(home_url('/post-two/'));
	}

	/**
	 * This is the controlled experiment to ensure certain outputs exist before minimizeHead() is called.
	 */
	public function test_controlled_experiment()
	{
		ob_start();
		wp_head();
		$headOutput = ob_get_clean();

		ob_start();
		wp_footer();
		$footerOutput = ob_get_clean();

		// For test_api()
		$this->assertTrue(strpos($headOutput, "<link rel='https://api.w.org/'") !== false);
		// For test_emoji()
		$this->assertTrue(strpos($headOutput, "<link rel='dns-prefetch' href='//s.w.org' />") !== false);
		$this->assertTrue(strpos($headOutput, "window._wpemojiSettings") !== false);
		$this->assertTrue(strpos($headOutput, "img.wp-smiley") !== false);
		// For test)generator()
		$this->assertTrue(strpos($headOutput, '<meta name="generator" content="WordPress') !== false);
		// For test_res_hint()
		$this->assertTrue(strpos($headOutput, "<link rel='dns-prefetch' href='//abc.def.com' />") !== false);
		// For test_rsd()
		$this->assertTrue(strpos($headOutput, '<link rel="EditURI" type="application/rsd+xml"') !== false);
		// For test_wlw()
		$this->assertTrue(strpos($headOutput, '<link rel="wlwmanifest" type="application/wlwmanifest+xml"') !== false);
		// For test_prev_next()
		$this->assertTrue(strpos($headOutput, "<link rel='prev'") !== false);
		$this->assertTrue(strpos($headOutput, "<link rel='next'") !== false);
		// These outputs appear in singular queries only.
		// For test_canonical()
		$this->assertTrue(strpos($headOutput, '<link rel="canonical"') !== false);
		// For test_extra_feed_links()
		$this->assertTrue(strpos($headOutput, '<link rel="alternate" type="application/rss+xml"') !== false);
		// For test_shortlink()
		$this->assertTrue(strpos($headOutput, "<link rel='shortlink'") !== false);
		// For test_wp_oembed()
		$this->assertTrue(strpos($headOutput, '<link rel="alternate" type="application/json+oembed"') !== false);
		$this->assertTrue(strpos($headOutput, '<link rel="alternate" type="text/xml+oembed"') !== false);
		$this->assertTrue(strpos($footerOutput, 'wp-embed.min.js') !== false);
	}

	public function test_api()
	{
		$args = $this->minimizeHeadArgs;
		$args['api'] = true;
		$u = new Utility($this->wp());
		$u->minimizeHead($args);
		ob_start();
		wp_head();
		$headOutput = ob_get_clean();
		$this->assertFalse(strpos($headOutput, "<link rel='https://api.w.org/'"), $headOutput);
	}

	public function test_canonical()
	{
		$args = $this->minimizeHeadArgs;
		$args['canonical'] = true;
		$u = new Utility($this->wp());
		$u->minimizeHead($args);
		ob_start();
		wp_head();
		$headOutput = ob_get_clean();
		$this->assertFalse(strpos($headOutput, '<link rel="canonical"'));
	}

	public function test_emoji()
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

	public function test_extra_feed_links()
	{
		$args = $this->minimizeHeadArgs;
		$args['extra_feed_links'] = true;
		$u = new Utility($this->wp());
		$u->minimizeHead($args);
		ob_start();
		wp_head();
		$headOutput = ob_get_clean();
		$this->assertFalse(strpos($headOutput, '<link rel="alternate" type="application/rss+xml"'));
	}

	public function test_generator()
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

	public function test_prev_next()
	{
		$args = $this->minimizeHeadArgs;
		$args['prev_next'] = true;
		$u = new Utility($this->wp());
		$u->minimizeHead($args);
		ob_start();
		wp_head();
		$headOutput = ob_get_clean();
		$this->assertFalse(strpos($headOutput, "<link rel='prev'"), $headOutput);
		$this->assertFalse(strpos($headOutput, "<link rel='next'"), $headOutput);
	}


	public function test_res_hint()
	{
		$args = $this->minimizeHeadArgs;
		$args['res_hint'] = true;
		$u = new Utility($this->wp());
		$u->minimizeHead($args);
		ob_start();
		wp_head();
		$headOutput = ob_get_clean();
		$this->assertFalse(strpos($headOutput, "<link rel='dns-prefetch' href='//abc.def.com' />"), $headOutput);
	}

	public function test_rsd()
	{
		$args = $this->minimizeHeadArgs;
		$args['rsd'] = true;
		$u = new Utility($this->wp());
		$u->minimizeHead($args);
		ob_start();
		wp_head();
		$headOutput = ob_get_clean();
		$this->assertFalse(strpos($headOutput, '<link rel="EditURI" type="application/rsd+xml"'), $headOutput);
	}

	public function test_shortlink()
	{
		$args = $this->minimizeHeadArgs;
		$args['shortlink'] = true;
		$u = new Utility($this->wp());
		$u->minimizeHead($args);
		ob_start();
		wp_head();
		$headOutput = ob_get_clean();
		$this->assertFalse(strpos($headOutput, "<link rel='shortlink'"), $headOutput);
	}

	public function test_wlw()
	{
		$args = $this->minimizeHeadArgs;
		$args['wlw'] = true;
		$u = new Utility($this->wp());
		$u->minimizeHead($args);
		ob_start();
		wp_head();
		$headOutput = ob_get_clean();
		$this->assertFalse(strpos($headOutput, '<link rel="wlwmanifest" type="application/wlwmanifest+xml"'), $headOutput);
	}

	public function test_wp_oembed()
	{
		$args = $this->minimizeHeadArgs;
		$args['wp_oembed'] = true;
		$u = new Utility($this->wp());
		$u->minimizeHead($args);
		ob_start();
		wp_head();
		$headOutput = ob_get_clean();
		ob_start();
		wp_footer();
		$footerOutput = ob_get_clean();
		$this->assertFalse(strpos($headOutput, '<link rel="alternate" type="application/json+oembed"'), $headOutput);
		$this->assertFalse(strpos($headOutput, '<link rel="alternate" type="text/xml+oembed"'), $headOutput);
		$this->assertFalse(strpos($footerOutput, 'wp-embed.min.js'), $footerOutput);
	}
}