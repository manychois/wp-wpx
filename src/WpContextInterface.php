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

	public function comments_open($post_id = null);

	public function get_avatar($id_or_email, $size = 96, $default = '', $alt = '', $args = null);

	public function get_comments_number($post_id = 0);

	public function get_nav_menu_locations();

    public function get_option($option, $default = false);

	public function get_post_type($post = null);

    public function get_the_ID();

    public function is_user_logged_in();

	public function paginate_comments_links($args = array());

	public function paginate_links($args = '');

	public function post_password_required($post = null);

	public function post_type_supports($post_type, $feature);

	public function remove_action($tag, $function_to_remove, $priority = 10);

	public function stripslashes_deep($value);

    public function wp_get_current_commenter();

	public function wp_get_nav_menu_items($menu, $args = array());

	public function wp_get_nav_menu_object($menu);

	public function wp_link_pages($args = '');

	public function wp_list_comments($args = array(), $comments = null);

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
