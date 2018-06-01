<?php
namespace Manychois\Wpx;

use WP_Term;

/**
 * Used to isolate direct access to WordPress built-in functions and global variables.
 */
interface WpContextInterface
{
	#region WordPress built-in functions

	public function __($text, $domain = 'default');

	public function add_filter($tag, $function_to_add, $priority = 10, $accepted_args = 1);

	public function get_nav_menu_locations();

	public function paginate_links( $args = '' );

	public function remove_action($tag, $function_to_remove, $priority = 10);

	public function stripslashes_deep($value);

	public function wp_get_nav_menu_items($menu, $args = array());

	public function wp_get_nav_menu_object($menu);

	public function wp_link_pages($args = '');

	public function wp_register_script($handle, $src, $deps = array(), $ver = false, $in_footer = false);

	public function wp_register_style($handle, $src, $deps = array(), $ver = false, $media = 'all');

	#endregion

	/**
	 * @return \WP_Query
	 */
	public function get_global_wp_query();

	/**
	 * @return \wpdb
	 */
	public function get_global_wpdb();
}
