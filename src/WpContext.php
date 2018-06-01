<?php
namespace Manychois\Wpx;

/**
 * Basic implementation of WpContextInterface which invokes corresponding WordPress functions.
 */
class WpContext implements WpContextInterface
{
	#region Manychois\Wpx\WpContextInterface Members

	public function __($text, $domain = 'default')
	{
		return \__($text, $domain);
	}

	public function add_filter($tag, $function_to_add, $priority = 10, $accepted_args = 1)
	{
		return \add_filter($tag, $function_to_add, $priority, $accepted_args);
	}

	public function get_nav_menu_locations()
	{
		return \get_nav_menu_locations();
	}

	public function paginate_links($args = '')
	{
		return \paginate_links($args);
	}

	public function remove_action($tag, $function_to_remove, $priority = 10)
	{
		return \remove_action($tag, $function_to_remove, $priority);
	}

	public function stripslashes_deep($value)
	{
		return \stripslashes_deep($value);
	}

	public function wp_get_nav_menu_items($menu, $args = array())
	{
		return \wp_get_nav_menu_items($menu, $args);
	}

	public function wp_get_nav_menu_object($menu)
	{
		return \wp_get_nav_menu_object($menu);
	}

	public function wp_link_pages($args = '')
	{
		return \wp_link_pages($args);
	}

	public function wp_register_script($handle, $src, $deps = array(), $ver = false, $in_footer = false)
	{
		return \wp_register_script($handle, $src, $deps, $ver, $in_footer);
	}

	public function wp_register_style($handle, $src, $deps = array(), $ver = false, $media = 'all')
	{
		return \wp_register_style($handle, $src, $deps, $ver, $media);
	}

	/**
	 * @return \WP_Query
	 */
	public function get_global_wp_query()
	{
		global $wp_query;
		return $wp_query;
	}

	/**
	 * @return \wpdb
	 */
	public function get_global_wpdb()
	{
		global $wpdb;
		return $wpdb;
	}

	#endregion
}