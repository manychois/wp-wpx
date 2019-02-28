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
     * Retrieve all comment information from WordPress functions.
     * It must be called within the tempalte file comments.php
     * @param array $args
     *     Optional. Array of arguments.
     *     "avatar_size" int Size that the avatar should be shown as, in pixels. Default is 32.
     *     "max_depth"   int The maximum comments depth. 0 for no restriction. Negative value for depth value set in admin screen. Default is -1.
     * @return CommentsInfo
     */
	public function getCommentsInfo(array $args = []) : CommentsInfo;

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
     * Gets the gallery info based on the parameters in filter post_gallery.
     * @param mixed $attrs
     * @param mixed $instance
     */
    public function getGallery($attrs, $instance) : Gallery;

	/**
     * Get the topmost menu item which contains the whole menu structure.
     * @param int|string $idOrLocation Menu id, or name of the theme location.
     * @return MenuItem Returns the topmost menu item.
     */
	public function getMenuItem($idOrLocation) : MenuItem;

	/**
     * Returns a list of paginated post links for paginated posts (i.e. includes the <!--nextpage--> Quicktag one or more times).
     * @param array $args
     *     Optional. Array of arguments.
     *     "next_or_number"   string Indicates whether page numbers should be used. Valid values are: number, next. Default 'number'.
     *     "nextpagelink"     string Text for link to next page. Default __('Next page').
     *     "previouspagelink" string Text for link to previous page. Default __('Previous page').
     * @return NavLink[]
     */
	public function getPaginatedPostLinks(array $args = []);

	/**
     * Returns a list of post pagination links.
     * See paginate_links for the argument usage.
     * @param array $args
     *     Optional. Array of arguments.
     *     "base"      string Used to reference the url, which will be used to create the paginated links. Default '%_%'.
     *     "format"    string Used for pagination structure. The default value is '?page=%#%', If using pretty permalinks this would be '/page/%#%'.
     *     "total"     int    The total amount of pages. Default is the number of pages the current query.
     *     "current"   int    The current page number.  Default is the current page number the current query.
     *     "show_all"  bool   If set to True, then it will show all of the pages instead of a short list of the pages near the current page. Default false.
     *     "end_size"  int    How many numbers on either the start and the end list edges. Default 1.
     *     "mid_size"  int    How many numbers to either side of current page, but not including current page. Default 2.
     *     "prev_next" bool   Whether to include the previous and next links in the list or not. Default true.
     *     "prev_text" string The previous page text. Works only if 'prev_next' argument is set to true. Default __('Previous').
     *     "next_text" string The next page text. Works only if 'prev_next' argument is set to true. Default __('Next').
     *     "add_args"  array  An array of query args to add. Default empty array.
     *     "add_fragment"string A string to append to each link. Default empty.
     * @return NavLink[]
     */
	public function getPostPaginationLinks(array $args = []);

    /**
     * Returns necessary info to render a search form.
     * @return SearchForm
     */
    public function getSearchForm() : SearchForm;

	/**
     * Remove certain WordPress default stuff in <head> tag.
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
     * @return void
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
