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
	}

	public function test_activate()
	{
		$u = new Utility($this->wp());
		$u->activate();
		$u->registerStyle('abc', ['href' => 'https://abc.localhost.com/style.css', 'crossorigin' => 'anonymous']);
		$u->registerScript('abc', ['src' => 'https://abc.localhost.com/script.js', 'defer', 'crossorigin' => 'anonymous']);

		wp_enqueue_style('abc');
		wp_enqueue_script('abc');

		ob_start();
		wp_head();
		$headerOutput = ob_get_clean();

		ob_start();
		wp_footer();
		$footerOutput = ob_get_clean();

		$this->assertTrue(strpos($headerOutput, '<link rel="stylesheet" href="https://abc.localhost.com/style.css" crossorigin="anonymous" />') !== false);
		$this->assertTrue(strpos($footerOutput, '<script src="https://abc.localhost.com/script.js" defer crossorigin="anonymous"></script>') !== false);
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

    public function test_getSearchForm()
    {
        $this->go_to(home_url('/?s=He%20says%20%22Oh!%22'));
		$u = new Utility($this->wp());
        $searchForm = $u->getSearchForm();
        $this->assertSame('http://example.org/', $searchForm->action);
        $this->assertSame('He says "Oh!"', $searchForm->query);
    }

    public function test_admin_css_js_registered()
    {
        $u = new Utility($this->wp());
        $u->activate();
        do_action('admin_enqueue_scripts');
        $this->assertTrue(wp_style_is('wpx-jquery-ui', 'registered'), 'jquery-ui not registered');
        $this->assertTrue(wp_script_is('wpx-codemirror', 'registered'), 'codemirror not registered');
    }
}