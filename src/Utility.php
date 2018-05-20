<?php
namespace Manychois\Wpx;

/**
 * A utility library for overriding WordPress default HTML output easily.
 */
class Utility implements UtilityInterface
{
	/**
	 * @var WpContextInterface
	 */
	private $wp;

	public function __construct(WpContextInterface $wp)
	{
		$this->wp = $wp;
	}

	#region Manychois\Wpx\UtilityInterface Members

	/**
	 * Return an approximate aspect ratio based on the width and height provided.
	 * Return empty if no common aspect ratio is matched.
	 * Supported ratios: 1x1, 4x3, 16x9, 21x9.
	 * @param int $width
	 * @param int $height
	 * @return string The closest aspect ratio to the specified width and height.
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
	 * @param string $name
	 * @param mixed $default Value when the name is not found. Default is null.
	 * @return mixed
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
	 * @param string $name
	 * @param mixed $default Value when the name is not found. Default is null.
	 * @return mixed
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

	#endregion
}