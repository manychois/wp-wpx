<?php
namespace Manychois\Wpx\Tests\Integration;

use Manychois\Wpx\Utility;

class UtilityTest extends IntegrationTestCase
{
	private $minimizeHeadArgs;

	public function setUp()
	{
		parent::setUp();
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
		$this->assertTrue(strpos($headOutput, "<link rel='https://api.w.org/'") !== false);
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
		$this->assertFalse(strpos($headOutput, "<link rel='https://api.w.org/'"), $headOutput);
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

	public function test_getMenuItem()
	{
		$menuId = wp_create_nav_menu('Test Menu');
		$locations = get_theme_mod('nav_menu_locations');
		$locations['testing-menu-location'] = $menuId;
		set_theme_mod('nav_menu_locations', $locations);

		$customLinkId = wp_update_nav_menu_item($menuId, 0, array(
			'menu-item-title' => 'Home',
			'menu-item-classes' => 'home test-item',
			'menu-item-url' => home_url('/'),
			'menu-item-status' => 'publish'));
		$pageId = $this->factory->post->create(['post_type' => 'page', 'post_title' => 'Testing Page', 'post_status' => 'publish', 'post_name' => 'testing-page']);
		$pageLinkId = wp_update_nav_menu_item($menuId, 0, array(
			'menu-item-type' => 'post_type',
			'menu-item-parent-id' => $customLinkId,
			'menu-item-object' => 'page',
			'menu-item-object-id' => $pageId,
			'menu-item-status' => 'publish'));
		$postId = $this->factory->post->create(['post_title' => 'Testing Post', 'post_status' => 'publish', 'post_name' => 'testing-post']);
		$postLinkId = wp_update_nav_menu_item($menuId, 0, array(
			'menu-item-type' => 'post_type',
			'menu-item-object' => 'post',
			'menu-item-object-id' => $postId,
			'menu-item-status' => 'publish'));
		$catLinkId = wp_update_nav_menu_item($menuId, 0, array(
			'menu-item-type' => 'taxonomy',
			'menu-item-parent-id' => $postLinkId,
			'menu-item-object' => 'category',
			'menu-item-object-id' => 1,
			'menu-item-status' => 'publish'));

		$this->go_to(home_url('/?cat=1/'));

		$u = new Utility($this->wp());
		$topMi = $u->getMenuItem($menuId);

		$mi = $topMi->children[0];
		$this->assertSame($customLinkId, $mi->id);
		$this->assertSame('custom', $mi->objectType);
		$this->assertSame('Home', $mi->label);
		$this->assertSame('home test-item', implode(' ', $mi->classes));
		$this->assertSame(home_url('/'), $mi->url);
		$this->assertFalse($mi->isCurrent);
		$this->assertFalse($mi->isCurrentParent);

		$mi = $mi->children[0];
		$this->assertSame($pageLinkId, $mi->id);
		$this->assertSame('page', $mi->objectType);
		$this->assertSame($pageId, $mi->objectId);
		$this->assertSame('post', $mi->objectBaseType);
		$this->assertSame('Testing Page', $mi->label);
		$this->assertSame(home_url('/testing-page/'), $mi->url);
		$this->assertFalse($mi->isCurrent);
		$this->assertFalse($mi->isCurrentParent);

		$mi = $topMi->children[1];
		$this->assertSame($postLinkId, $mi->id);
		$this->assertSame('post', $mi->objectType);
		$this->assertSame($postId, $mi->objectId);
		$this->assertSame('post', $mi->objectBaseType);
		$this->assertSame('Testing Post', $mi->label);
		$this->assertSame(home_url('/testing-post/'), $mi->url);
		$this->assertFalse($mi->isCurrent);
		$this->assertTrue($mi->isCurrentParent);

		$mi = $mi->children[0];
		$this->assertSame($catLinkId, $mi->id);
		$this->assertSame('category', $mi->objectType);
		$this->assertSame(1, $mi->objectId);
		$this->assertSame('taxonomy', $mi->objectBaseType);
		$this->assertSame('Uncategorized', $mi->label);
		$this->assertSame(home_url('/?cat=1'), $mi->url);
		$this->assertTrue($mi->isCurrent);
		$this->assertFalse($mi->isCurrentParent);

		$this->go_to(home_url('/testing-page/'));

		$topMi = $u->getMenuItem($menuId);
		$mi = $topMi->children[0];
		$this->assertFalse($mi->isCurrent);
		$this->assertTrue($mi->isCurrentParent);

		$mi = $mi->children[0];
		$this->assertTrue($mi->isCurrent);
		$this->assertFalse($mi->isCurrentParent);

		$topMi = $u->getMenuItem('testing-menu-location');
		$mi = $topMi->children[0];
		$this->assertFalse($mi->isCurrent);
		$this->assertTrue($mi->isCurrentParent);

		$mi = $mi->children[0];
		$this->assertTrue($mi->isCurrent);
		$this->assertFalse($mi->isCurrentParent);
	}
}