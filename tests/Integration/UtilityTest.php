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