<?php
namespace Manychois\Wpx;

/**
 * A utility library for overriding WordPress default HTML output easily.
 */
class Utility implements UtilityInterface
{
	/**
	 * @var array
	 */
	private $scriptAttrs;
	/**
	 * @var array
	 */
	private $styleAttrs;
	/**
	 * @var WpContextInterface
	 */
	private $wp;

	public function __construct(WpContextInterface $wp)
	{
		$this->wp = $wp;
		$this->scriptAttrs = [];
		$this->styleAttrs = [];
	}

	/**
	 * @codeCoverageIgnore
	 * Setup necessary WordPress hooks
	 */
	public function activate()
	{
		$wp = $this->wp;
		$wp->add_filter('script_loader_tag', array($this, 'script_loader_tag'), 10, 3);
		$wp->add_filter('style_loader_tag', array($this, 'style_loader_tag'), 10, 3);
	}

	#region Manychois\Wpx\UtilityInterface Members

	/**
	 * Return an approximate aspect ratio based on the width and height provided.
	 * Return empty if no common aspect ratio is matched.
	 * Supported ratios: 1x1, 4x3, 16x9, 21x9.
	 * @param int $width  Width of the media.
	 * @param int $height Height of the media.
	 * @return string Returns the closest aspect ratio to the specified width and height.
	 */
	function findAspectRatio(int $width, int $height) : string
	{
		if ($width === 0 || $height === 0) return '';
		$ratio = $width / $height;
		/**
		 * 1x1  =  1
		 * 4x3  ~= 1.333333333
		 * 16x9 ~= 1.777777778
		 * 21x9 ~= 2.333333333
		 * Pick +/- 0.15 as acceptable range
		 */

		if ($ratio < 0.85) {
			$aspectRatio = '';
		} else if ($ratio <= 1.15) {
			$aspectRatio = '1x1';
		} else if ($ratio < 1.1833) {
			$aspectRatio = '';
		} else if ($ratio <= 1.4833) {
			$aspectRatio = '4x3';
		} else if ($ratio < 1.6278) {
			$aspectRatio = '';
		} else if ($ratio <= 1.9278) {
			$aspectRatio = '16x9';
		} else if ($ratio < 2.1833) {
			$aspectRatio = '';
		} else if ($ratio <= 2.4833) {
			$aspectRatio = '21x9';
		} else {
			$aspectRatio = '';
		}
		return $aspectRatio;
	}

	/**
	 * Safe get the value from $_GET. The value is stripped to undo WordPress default slash insertion.
	 * @param string $name    Name of the variable.
	 * @param mixed  $default Value when the name is not found. Default null.
	 * @return mixed Returns stripped value of the variable.
	 */
	public function getFromGet(string $name, $default = null)
	{
		if (isset($_GET[$name])) {
			return $this->wp->stripslashes_deep($_GET[$name]);
		} else {
			return $default;
		}
	}

	/**
	 * Safe get the value from $_POST. The value is stripped to undo WordPress default slash insertion.
	 * @param string $name    Name of the variable.
	 * @param mixed  $default Value when the name is not found. Default null.
	 * @return mixed Returns stripped value of the variable.
	 */
	public function getFromPost(string $name, $default = null)
	{
		if (isset($_POST[$name])) {
			return $this->wp->stripslashes_deep($_POST[$name]);
		} else {
			return $default;
		}
	}

	/**
	 * Get the topmost menu item which contains the whole menu structure.
	 * @param int|string $idOrLocation Menu id, or name of the theme location.
	 * @return MenuItem Returns the topmost menu item.
	 */
	public function getMenuItem($idOrLocation) : MenuItem
	{
		$top = new MenuItem();
		$wp = $this->wp;
		$wp_query = $wp->get_global_wp_query();
		$queried_object_id = intval($wp_query->queried_object_id);
		if (is_int($idOrLocation)) {
			$menu = $wp->wp_get_nav_menu_object($idOrLocation);
		} else {
			$locations = $wp->get_nav_menu_locations();
			$menu = $wp->wp_get_nav_menu_object($locations[$idOrLocation]);
		}
		if ($menu === false) return $top;

		$menu_items = $wp->wp_get_nav_menu_items($menu->term_id, ['update_post_term_cache' => false]);
		$lookup = [$top];
		$missingLabelPosts = [];
		$missingLabelTaxonomies = [];
		$currents = [];

		foreach ($menu_items as $wp_mi) {
			$mi = new MenuItem();
			$mi->id = intval($wp_mi->ID);
			$mi->objectId = intval($wp_mi->object_id);
			$mi->objectType = $wp_mi->object;
			switch ($wp_mi->type) {
				case 'taxonomy': $mi->objectBaseType = 'taxonomy'; break;
				case 'post_type': $mi->objectBaseType = 'post'; break;
			}
			$mi->label = $wp_mi->post_title;
			$mi->title = $wp_mi->post_excerpt;
			$mi->target = $wp_mi->target;
			$mi->xfn = $wp_mi->xfn;
			$mi->description = $wp_mi->post_content;
			$mi->url = $wp_mi->url;
			if ($wp_mi->classes && $wp_mi->classes[0] !== '') {
				$mi->classes = $wp_mi->classes;
			}
			if ($queried_object_id === $mi->objectId) {
				$mi->isCurrent = true;
				$currents[] = $mi;
			}
			$lookup[$mi->id] = $mi;
			if ($mi->label === '') {
				if ($mi->objectBaseType === 'post') {
					$missingLabelPosts[$mi->objectId] = $mi;
				} else if ($mi->objectBaseType === 'taxonomy') {
					$missingLabelTaxonomies[$mi->objectId] = $mi;
				}
			}
		}

		foreach ($menu_items as $wp_mi) {
			$child = $lookup[$wp_mi->ID];
			$parent = $lookup[$wp_mi->menu_item_parent];
			$child->parent = $parent;
			$parent->children[] = $child;
		}
		unset($lookup);

		$lookup = $top->children;
		while (!empty($lookup)) {
			$mi = $lookup[0];
			$mi->depth = $mi->parent->depth + 1;
			array_shift($lookup);
			$lookup = array_merge($lookup, $mi->children);
		}

		$wpdb = $wp->get_global_wpdb();
		if (!empty($missingLabelPosts)) {
			$sql = sprintf('SELECT ID, post_title FROM %s WHERE ID IN (%s)', $wpdb->posts, implode(',', array_keys($missingLabelPosts)));
			$rows = $wpdb->get_results($sql);
			foreach ($rows as $r) {
				$missingLabelPosts[$r->ID]->label = $r->post_title;
			}
			unset($missingLabelPosts);
		}
		if (!empty($missingLabelTaxonomies)) {
			$sql = sprintf('SELECT term_id, name FROM %s WHERE term_id IN (%s)', $wpdb->terms, implode(',', array_keys($missingLabelTaxonomies)));
			$rows = $wpdb->get_results($sql);
			foreach ($rows as $r) {
				$missingLabelTaxonomies[$r->term_id]->label = $r->name;
			}
			unset($missingLabelTaxonomies);
		}

		while (!empty($currents)) {
			$parent = $currents[0]->parent;
			if ($parent->id === 0) break;
			$parent->isCurrentParent = true;
			$currents[] = $parent;
			array_shift($currents);
		}

		error_log(print_r($top, true));
		return $top;
	}

	/**
	 * @codeCoverageIgnore
	 * Reduce unnecessary WordPress default stuff in <head> tag.
	 * @param array $args
	 *     Optional. Array of arguments.
	 *     "admin_bar"        bool Set true to remove the frontend admin bar. Default false.
	 *     "api"              bool Set true to remove WP REST API link tag. Default true.
	 *     "canonical"        bool Set true to remove canonical link tag. Default false.
	 *     "emoji"            bool Set true to remove emoji related style and javascript. Default true.
	 *     "extra_feed_links" bool Set true to remove automatic feed link tags. Default true.
	 *     "generator"        bool Set true to remove WordPress version meta tag. Default true.
	 *     "prev_next"        bool Set true to remove links to the next and previous post. Default false.
	 *     "res_hint"         bool Set true to remove DNS prefetch link tag. Default false.
	 *     "rsd"              bool Set true to remove EditURI/RSD link tag. Default true.
	 *     "shortlink"        bool Set true to remove Shortlink link tag. Default true.
	 *     "wlw"              bool Set true to remove Windows Live Writer Manifest link tag. Default true.
	 *     "wp_oembed"        bool Set true to remove Embed discovery link tag and related javascript. Default true.
	 */
	public function minimizeHead(array $args = [])
	{
		$defaults = [
			'admin_bar' => false,
			'api' => true,
			'canonical' => false,
			'emoji' => true,
			'extra_feed_links' => true,
			'generator' => true,
			'prev_next' => false,
			'res_hint' => false,
			'rsd' => true,
			'shortlink' => true,
			'wlw' => true,
			'wp_oembed' => true
		];
		$args = array_merge($defaults, $args);
		if ($args['admin_bar']) {
			$this->wp->add_filter('show_admin_bar', '__return_false');
		}
		if ($args['api']) {
			$this->wp->remove_action('wp_head', 'rest_output_link_wp_head', 10);
		}
		if ($args['canonical']) {
			$this->wp->remove_action('wp_head', 'rel_canonical');
		}
		if ($args['emoji']) {
			$this->wp->remove_action('wp_head', 'print_emoji_detection_script', 7);
			$this->wp->remove_action('wp_print_styles', 'print_emoji_styles');
			$this->wp->add_filter('emoji_svg_url', '__return_false'); // Remove s.w.org prefetch link
		}
		if ($args['extra_feed_links']) {
			$this->wp->remove_action('wp_head', 'feed_links_extra', 3);
		}
		if ($args['generator']) {
			$this->wp->remove_action('wp_head', 'wp_generator');
			$this->wp->add_filter('the_generator', '__return_false'); // Removes the generator name from the RSS feeds.
		}
		if ($args['prev_next']) {
			$this->wp->remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');
		}
		if ($args['res_hint']) {
			$this->wp->remove_action('wp_head', 'wp_resource_hints', 2);
		}
		if ($args['rsd']) {
			$this->wp->remove_action('wp_head', 'rsd_link');
		}
		if ($args['shortlink']) {
			$this->wp->remove_action('wp_head', 'wp_shortlink_wp_head');
		}
		if ($args['wlw']) {
			$this->wp->remove_action('wp_head', 'wlwmanifest_link');
		}
		if ($args['wp_oembed']) {
			$this->wp->remove_action('wp_head', 'wp_oembed_add_discovery_links');
			$this->wp->remove_action('wp_head', 'wp_oembed_add_host_js');
		}
	}

	/**
	 * Initialize a tag builder.
	 * @param string $tagName Node name of the element.
	 * @return TagBuilder Returns tag builder with tag name initialized.
	 */
	public function newTag(string $tagName) : TagBuilder
	{
		$tb = new TagBuilder($tagName);
		return $tb;
	}

	/**
	 * Register a new script.
	 * @param string $handle   Name of the script. Should be unique.
	 * @param array  $attrs    Associative array of HTML atrributes of the style link tag. Attribute src must be present.
	 * @param array  $deps     Optional. An array of registered script handles this script depends on. Default empty array.
	 * @param bool   $inFooter Optional. Set true to place script tag before </body>, or false to place it inside <head>.
	 *                         Default true. Note that it is different from WordPress default value.
	 * @return void
	 */
	public function registerScript(string $handle, array $attrs, array $deps = array(), bool $inFooter = true) {
		$src = $attrs['src'];
		$this->wp->wp_register_script($handle, $src, $deps, null, $inFooter);
		$this->scriptAttrs[$handle] = $attrs;
	}

	/**
	 * Register a new style.
	 * @param string $handle Name of the stylesheet. Should be unique.
	 * @param array  $attrs  Associative array of HTML atrributes of the style link tag. Attribute href must be present.
	 * @param array  $deps   Optional. An array of registered stylesheet handles this stylesheet depends on. Default empty array.
	 * @return void
	 */
	public function registerStyle(string $handle, array $attrs, array $deps = array())
	{
		$src = $attrs['href'];
		$this->wp->wp_register_style($handle, $src, $deps, null);
		$this->styleAttrs[$handle] = $attrs;
	}

	#endregion

	#region WordPress hooks

	public function script_loader_tag(string $tag, string $handle, string $src) : string
	{
		if (array_key_exists($handle, $this->scriptAttrs)) {
			$attrs = $this->scriptAttrs[$handle];
			$script = $this->newTag('script')->setAttr($attrs)->append('');
			$oldTag = "<script type='text/javascript' src='$src'></script>";
			$tag = str_replace($oldTag, $script, $tag);
		}
		return $tag;
	}

	public function style_loader_tag(string $html, string $handle, string $href) : string
	{
		if (array_key_exists($handle, $this->styleAttrs)) {
			$attrs = $this->styleAttrs[$handle];
			$attrs = array_merge(['rel' => 'stylesheet'], $attrs);
			$html = $this->newTag('link')->setAttr($attrs) . "\n";
		}
		return $html;
	}

	#endregion
}