<?php
namespace Manychois\Wpx;

/**
 * A utility library for overriding WordPress default HTML output easily.
 */
interface UtilityInterface
{
	/**
	 * Return an approximate aspect ratio based on the width and height provided.
	 * Return empty if no common aspect ratio is matched.
	 * Supported ratios: 1x1, 4x3, 16x9, 21x9.
	 * @param int $width  Width of the media.
	 * @param int $height Height of the media.
	 * @return string Returns the closest aspect ratio to the specified width and height.
	 */
	public function findAspectRatio(int $width, int $height) : string;

	/**
	 * Safe get the value from $_GET. The value is stripped to undo WordPress default slash insertion.
	 * @param string $name    Name of the variable.
	 * @param mixed  $default Value when the name is not found. Default null.
	 * @return mixed Returns stripped value of the variable.
	 */
	public function getFromGet(string $name, $default = null);

	/**
	 * Safe get the value from $_POST. The value is stripped to undo WordPress default slash insertion.
	 * @param string $name    Name of the variable.
	 * @param mixed  $default Value when the name is not found. Default null.
	 * @return mixed Returns stripped value of the variable.
	 */
	public function getFromPost(string $name, $default = null);

	/**
	 * Get the topmost menu item which contains the whole menu structure.
	 * @param int|string $idOrLocation Menu id, or name of the theme location.
	 * @return MenuItem Returns the topmost menu item.
	 */
	public function getMenuItem($idOrLocation) : MenuItem;

	/**
	 * Returns a list of paginated post links.
	 * See wp_link_pages for the arguemtn usage.
	 * @param array $args
	 * @return NavLink[]
	 */
	public function getPaginatedPostLinks(array $args = []);

	/**
	 * Returns a list of post pagination links.
	 * See paginate_links for the argument usage.
	 * @param array $args
	 * @return NavLink[]
	 */
	public function getPostPaginationLinks(array $args = []);

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
	public function minimizeHead(array $args = []);

	/**
	 * Initialize a tag builder.
	 * @param string $tagName Node name of the element.
	 * @return TagBuilder Returns tag builder with tag name initialized.
	 */
	public function newTag(string $tagName) : TagBuilder;

	/**
	 * Register a new script.
	 * @param string $handle   Name of the script. Should be unique.
	 * @param array  $attrs    Associative array of HTML atrributes of the style link tag. Attribute src must be present.
	 * @param array  $deps     Optional. An array of registered script handles this script depends on. Default empty array.
	 * @param bool   $inFooter Optional. Set true to place script tag before </body>, or false to place it inside <head>.
	 *                         Default true. Note that it is different from WordPress default value.
	 * @return void
	 */
	public function registerScript(string $handle, array $attrs, array $deps = array(), bool $inFooter = true);

	/**
	 * Register a new style.
	 * @param string $handle Name of the stylesheet. Should be unique.
	 * @param array  $attrs  Associative array of HTML atrributes of the style link tag. Attribute href must be present.
	 * @param array  $deps   Optional. An array of registered stylesheet handles this stylesheet depends on. Default empty array.
	 * @return void
	 */
	public function registerStyle(string $handle, array $attrs, array $deps = array());
}
