<?php
namespace Manychois\Wpx;
use Manychois\Views\Esc;
use IvoPetkov\HTML5DOMDocument;
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
     * Setups necessary WordPress hooks
     */
	public function activate()
	{
		$wp = $this->wp;
		$wp->add_filter('script_loader_tag', [$this, 'script_loader_tag'], 10, 3);
		$wp->add_filter('style_loader_tag', [$this, 'style_loader_tag'], 10, 3);
        $wp->add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts'], 5);
	}

	#region Manychois\Wpx\UtilityInterface Members

	/**
     * Returns an approximate aspect ratio based on the width and height provided.
     * Returns empty if no common aspect ratio is matched.
     * Supported ratios: 1x1, 4x3, 16x9, 21x9.
     * @param int $width  Width of the media.
     * @param int $height Height of the media.
     * @return string Returns the closest aspect ratio to the specified width and height.
     */
	public function findAspectRatio(int $width, int $height) : string
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
     * Retrieve all comment information from WordPress functions.
     * It must be called within the tempalte file comments.php
     * @param array $args
     *     Optional. Array of arguments.
     *     "avatar_size" int Size that the avatar should be shown as, in pixels. Default is 32.
     *     "max_depth"   int The maximum comments depth. 0 for no restriction. Negative value for depth value set in admin screen. Default is -1.
     * @return CommentsInfo
     */
	public function getCommentsInfo(array $args = []) : CommentsInfo {
		$wp = $this->wp;
		$args = array_merge([
			'avatar_size' => 32,
			'max_depth' => -1
		], $args);
		$avatarSize = intval($args['avatar_size']);
		$cInfo = new CommentsInfo();
		$cInfo->commentCount = $wp->get_comments_number();
		$cInfo->isCommentAllowed = $wp->comments_open();
		$cInfo->isCommentSupported = $wp->post_type_supports($wp->get_post_type(), 'comments');
		$cInfo->isPasswordRequired = $wp->post_password_required();
        $cInfo->isRegistrationRequired = boolval($wp->get_option('comment_registration'));

		$parent = $cInfo->topComment;
		$prevComment = null;
		$callback = function($comment, $args, $depth) use (&$wp, $avatarSize, &$parent, &$prevComment) {
			$c = new Comment();
			$c->id = $comment->comment_ID;
			$c->isApproved = $comment->comment_approved != '0';
			$c->content = $comment->comment_content;
			$c->time = $comment->comment_date;
			$c->timeUtc = $comment->comment_date_gmt;
			$c->author = new CommentAuthor();
			$c->author->name = $comment->comment_author;
			$c->author->email = $comment->comment_author_email;
			$c->author->url = $comment->comment_author_url;
			$c->author->ip = $comment->comment_author_IP;
			$c->author->avatarHtml = $wp->get_avatar($comment, $avatarSize);
			$c->depth = $depth;
			$diff = $c->depth - $parent->depth;
			if ($diff === 0) {
				$parent = $parent->parent;
			} elseif ($diff === 2) {
				$parent = $prevComment;
			}
			$c->parent = $parent;
			$parent->children[] = $c;
			$prevComment = $c;
		};

		$wp->wp_list_comments(['callback' => $callback, 'end-callback' => '__return_empty_string']);
		$cInfo->paginationLinks = $this->getCommentPaginationLinks();

        $askForLogin = $cInfo->isRegistrationRequired && !$wp->is_user_logged_in();
        if ($cInfo->isCommentAllowed && $cInfo->isCommentSupported && !$cInfo->isPasswordRequired && !$askForLogin) {
            $cInfo->commentForm = new CommentForm();
            if (!$wp->is_user_logged_in()) {
                $commentCookies = $wp->wp_get_current_commenter();
                $requireNameEmail = $wp->get_option('require_name_email');

                $authorInput = (new TagBuilder('input'))->setAttr([
                    'name' => 'author',
                    'type' => 'text',
                    'maxlength' => '245',
                    'value' => Esc::html($commentCookies['comment_author'])
                ]);
                if ($requireNameEmail) $authorInput->setAttr(['required']);
                $cInfo->commentForm->inputFields['author'] = $authorInput;

                $emailInput = (new TagBuilder('input'))->setAttr([
                    'name' => 'email',
                    'type' => 'email',
                    'maxlength' => '100',
                    'value' => Esc::html($commentCookies['comment_author_email'])
                ]);
                if ($requireNameEmail) $emailInput->setAttr(['required']);
                $cInfo->commentForm->inputFields['email'] = $emailInput;

                $urlInput = (new TagBuilder('input'))->setAttr([
                    'name' => 'url',
                    'type' => 'url',
                    'maxlength' => '200',
                    'value' => Esc::html($commentCookies['comment_author_url'])
                ]);
                $cInfo->commentForm->inputFields['url'] = $urlInput;

                $cookiesInput = (new TagBuilder('input'))->setAttr([
                    'name' => 'wp-comment-cookies-consent',
                    'type' => 'checkbox',
                    'value' => 'yes'
                ]);
                if (!empty($commentCookies['comment_author_email'])) $cookiesInput->setAttr(['checked']);
                $cInfo->commentForm->inputFields['wp-comment-cookies-consent'] = $cookiesInput;
            }

            $commentInput = (new TagBuilder('textarea'))->setAttr([
                'name' => 'comment',
                'maxlength' => 65525,
                'required'
            ]);
            $commentInput->append('');
            $cInfo->commentForm->inputFields['comment'] = $commentInput;

            $hiddenPostId = (new TagBuilder('input'))->setAttr([
                'name' => 'comment_post_ID',
                'type' => 'hidden',
                'value' => $wp->get_the_ID()
            ]);
            $cInfo->commentForm->hiddenFields['comment_post_ID'] = $hiddenPostId;

            $replyToId = intval($this->getFromGet('replytocom', '0'));
            $hiddenParentId = (new TagBuilder('input'))->setAttr([
                'name' => 'comment_parent',
                'type' => 'hidden',
                'value' => $replyToId
            ]);
            $cInfo->commentForm->hiddenFields['comment_parent'] = $hiddenParentId;
        }

		return $cInfo;
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
     * Safe gets the value from $_POST. The value is stripped to undo WordPress default slash insertion.
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
     * Gets the gallery info based on the parameters in filter post_gallery.
     * @param mixed $attrs
     * @param mixed $instance
     */
    public function getGallery($attrs, $instance) : Gallery
    {
        $wp = $this->wp;
        $g = new Gallery();
        $post = $wp->get_post();
        $atts = $wp->shortcode_atts([
            'order'      => 'ASC',
            'orderby'    => 'menu_order ID',
            'id'         => $post ? $post->ID : 0,
            'columns'    => 3,
            'size'       => 'thumbnail',
            'include'    => '',
            'exclude'    => '',
            'link'       => ''
        ], $attrs, 'gallery');
        $g->attrs = $atts;

        $id = intval($atts['id']);
        $attachments = [];
        if (!empty($atts['include'])) {
            $_attachments = $wp->get_posts([
                'include'        => $atts['include'],
                'post_status'    => 'inherit',
                'post_type'      => 'attachment',
                'post_mime_type' => 'image',
                'order'          => $atts['order'],
                'orderby'        => $atts['orderby']
            ]);
            foreach ($_attachments as $key => $val) {
                $attachments[$val->ID] = $_attachments[$key];
            }
        } elseif (!empty($atts['exclude'])) {
            $attachments = $wp->get_children([
                'post_parent'    => $id,
                'exclude'        => $atts['exclude'],
                'post_status'    => 'inherit',
                'post_type'      => 'attachment',
                'post_mime_type' => 'image',
                'order'          => $atts['order'],
                'orderby'        => $atts['orderby']
            ]);
        } else {
            $attachments = $wp->get_children([
                'post_parent'    => $id,
                'post_status'    => 'inherit',
                'post_type'      => 'attachment',
                'post_mime_type' => 'image',
                'order'          => $atts['order'],
                'orderby'        => $atts['orderby']
            ]);
        }
        if (empty($attachments)) return $g;

        $upload_dir = rtrim($wp->wp_get_upload_dir()['baseurl'], '/');
        foreach ($attachments as $id => $attachment) {
            $meta = wp_get_attachment_metadata($id);
            $dirname = _wp_get_attachment_relative_path($meta['file'] );
            $imageBaseUrl = rtrim("$upload_dir/$dirname", '/');
            $gi = new GalleryItem();
            $gi->sizes = $meta['sizes'];
            foreach ($gi->sizes as &$v) {
                $v['url'] = $imageBaseUrl . '/' . $v['file'];
                unset($v['file']);
            }
            $gi->id = $id;
            $gi->alt = $wp->get_post_meta($id, '_wp_attachment_image_alt', true);
            $gi->caption = $attachment->post_excerpt;
            $gi->description = $attachment->post_content;
            $gi->height = intval($meta['height']);
            $gi->width = intval($meta['width']);
            $gi->postUrl = $wp->get_attachment_link($id);
            $gi->title = $attachment->post_title;
            $gi->url = $upload_dir . '/' . $meta['file'];
            $g->items[] = $gi;
        }
        return $g;
    }

	/**
     * Gets the topmost menu item which contains the whole menu structure.
     * @param int|string $idOrLocation Menu id, or name of the theme location.
     * @return MenuItem Returns the topmost menu item.
     */
	public function getMenuItem($idOrLocation) : MenuItem
	{
		$top = new MenuItem();
		$wp = $this->wp;
		$wp_query = $wp->get_global_wp_query();
		$qObj = $wp_query->get_queried_object();
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
			if ($mi->objectBaseType === 'post') {
				if (isset($qObj->post_type) && $qObj->ID === $mi->objectId) {
					$mi->isCurrent = true;
					$currents[] = $mi;
				}
				if ($mi->label === '') {
					$missingLabelPosts[$mi->objectId] = $mi;
				}
			} else if ($mi->objectBaseType === 'taxonomy') {
				if (isset($qObj->term_id) && $qObj->term_id === $mi->objectId) {
					$mi->isCurrent = true;
					$currents[] = $mi;
				}
				if ($mi->label === '') {
					$missingLabelTaxonomies[$mi->objectId] = $mi;
				}
			}
			$lookup[$mi->id] = $mi;
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

		return $top;
	}

	/**
     * Returns a list of paginated post links for paginated posts (i.e. includes the <!--nextpage--> Quicktag one or more times).
     * @param array $args
     *     Optional. Array of arguments.
     *     "next_or_number"   string Indicates whether page numbers should be used. Valid values are: number, next. Default 'number'.
     *     "nextpagelink"     string Text for link to next page. Default __('Next page').
     *     "previouspagelink" string Text for link to previous page. Default __('Previous page').
     * @return NavLink[]
     */
	public function getPaginatedPostLinks(array $args = [])
	{
		$prevNext = array_merge([
			'nextpagelink' => $this->wp->__('Next page'),
			'previouspagelink' => $this->wp->__('Previous page')
		], $args);
		$args = array_merge($args, [
			'before' => '',
			'after' => '',
			'link_before' => '<span>',
			'link_after' => '</span>',
			'nextpagelink' => 'NEXT',
			'previouspagelink' => 'PREV',
            'pagelink' => '%',
            'separator' => ' ',
			'echo' => false
		]);
		$output = $this->wp->wp_link_pages($args);
		$pLinks = [];
		if ($output) {
			$dom = new HTML5DOMDocument();
			$dom->loadHTML($output);
			$eBody = $dom->querySelector('body');
			foreach ($eBody->childNodes as $n) {
				if ($n->nodeType !== XML_ELEMENT_NODE) continue;
				$e = Type::DomElement($n);
				if ($e->tagName === 'a') {
					$href = $e->getAttribute('href');
					$text = $e->childNodes->item(0)->innerHTML;
					$type = NavLink::PAGE;
					if ($text === 'NEXT') {
						$type = NavLink::NEXT;
						$text = $prevNext['nextpagelink'];
					} else if ($text === 'PREV') {
						$type = NavLink::PREV;
						$text = $prevNext['previouspagelink'];
					}
					$pLinks[] = new NavLink($type, $href, $text);
				} else if ($e->tagName === 'span') {
					$pLinks[] = new NavLink(NavLink::CURRENT, null, $e->innerHTML);
				}
			}
		}
		return $pLinks;
	}

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
	public function getPostPaginationLinks(array $args = []) {
		$prevNext = array_merge([
			'prev_text' => $this->wp->__('Previous'),
			'next_text' => $this->wp->__('Next')
		], $args);
		$args = array_merge($args, [
			'prev_text' => 'PREV',
			'next_text' => 'NEXT',
			'type' => 'plain',
			'before_page_number' => '',
			'after_page_number' => ''
		]);

		$output = $this->wp->paginate_links($args);
		$pLinks = [];
		if ($output) {
			$dom = new HTML5DOMDocument();
			$dom->loadHTML($output);
			$eBody = $dom->querySelector('body');
			foreach ($eBody->childNodes as $n) {
				if ($n->nodeType !== XML_ELEMENT_NODE) continue;
				$e = Type::DomElement($n);
				$text = $e->innerHTML;
				if ($e->tagName === 'a') {
					$href = $e->getAttribute('href');
					if ($text === 'PREV') {
						$pLinks[] = new NavLink(NavLink::PREV, $href, $prevNext['prev_text']);
					} else if ($text === 'NEXT') {
						$pLinks[] = new NavLink(NavLink::NEXT, $href, $prevNext['next_text']);
					} else {
						$pLinks[] = new NavLink(NavLink::PAGE, $href, $text);
					}
				} else if ($e->tagName === 'span') {
					$class = $e->getAttribute('class');
					if (strpos($class, 'dots') !== false) {
						$pLinks[] = new NavLink(NavLink::ELLIPSIS);
					} else if (strpos($class, 'current') !== false) {
						$pLinks[] = new NavLink(NavLink::CURRENT, null, $text);
					}
				}
			}
		}
		return $pLinks;
	}

    /**
     * Returns necessary info to render a search form.
     * @return SearchForm
     */
    public function getSearchForm() : SearchForm
    {
        $wp = $this->wp;
        $sf = new SearchForm();
        $sf->action = $wp->home_url('/');
        $sf->query = $wp->get_search_query(false);
        return $sf;
    }

	/**
     * Remove certain WordPress default stuff in <head> tag.
     * @param array $args
     *     Optional. Array of arguments.
     *     "admin_bar"        bool Set true to remove the frontend admin bar. Default false.
     *     "api"              bool Set true to remove WP REST API link tag. Default true.
     *     "canonical"        bool Set true to remove canonical link tag. Default false.
     *     "common_block"     bool Set true to remove WordPress block styles Default true.
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
	public function minimizeHead(array $args = [])
	{
		$defaults = [
			'admin_bar' => false,
			'api' => true,
			'canonical' => false,
            'common_block' => true,
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

        $wp = $this->wp;

		if ($args['admin_bar']) {
			$wp->add_filter('show_admin_bar', '__return_false');
		}
		if ($args['api']) {
			$wp->remove_action('wp_head', 'rest_output_link_wp_head', 10);
		}
		if ($args['canonical']) {
			$wp->remove_action('wp_head', 'rel_canonical');
		}
		if ($args['common_block']) {
            $wp->remove_action('wp_enqueue_scripts', 'wp_common_block_scripts_and_styles');
        }
        if ($args['emoji']) {
			$wp->remove_action('wp_head', 'print_emoji_detection_script', 7);
			$wp->remove_action('wp_print_styles', 'print_emoji_styles');
			$wp->add_filter('emoji_svg_url', '__return_false'); // Remove s.w.org prefetch link
		}
		if ($args['extra_feed_links']) {
			$wp->remove_action('wp_head', 'feed_links_extra', 3);
		}
		if ($args['generator']) {
			$wp->remove_action('wp_head', 'wp_generator');
			$wp->add_filter('the_generator', '__return_false'); // Removes the generator name from the RSS feeds.
		}
		if ($args['prev_next']) {
			$wp->remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');
		}
		if ($args['res_hint']) {
			$wp->remove_action('wp_head', 'wp_resource_hints', 2);
		}
		if ($args['rsd']) {
			$wp->remove_action('wp_head', 'rsd_link');
		}
		if ($args['shortlink']) {
			$wp->remove_action('wp_head', 'wp_shortlink_wp_head');
		}
		if ($args['wlw']) {
			$wp->remove_action('wp_head', 'wlwmanifest_link');
		}
		if ($args['wp_oembed']) {
			$wp->remove_action('wp_head', 'wp_oembed_add_discovery_links');
			$wp->remove_action('wp_head', 'wp_oembed_add_host_js');
		}
	}

	/**
     * Initializes a tag builder.
     * @param string $tagName Node name of the element.
     * @return TagBuilder Returns tag builder with tag name initialized.
     */
	public function newTag(string $tagName) : TagBuilder
	{
		$tb = new TagBuilder($tagName);
		return $tb;
	}

	/**
     * Registers a new script.
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
     * Registers a new style.
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

    public function admin_enqueue_scripts()
    {
        $this->registerStyle('wpx-jquery-ui', [
            'href' => 'https://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.min.css',
            'integrity' => 'sha384-A/CgvDCSM2jOpa4G++hlEtzweAjg53uGXJLUnen/qb5faVDcN+vaHrL5czAdzhK8',
            'crossorigin' => 'anonymous'
        ]);
        $this->wp->wp_add_inline_style('wpx-jquery-ui', '.ui-widget-overlay{z-index:100000!important;}');

        $this->registerScript('wpx-codemirror', [
            'src' => 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.40.0/codemirror.min.js',
            'integrity' => 'sha256-bRw9NTR0/nKmhEQc8jg716nnkA6EwKx2C46i01QGKpc=',
            'crossorigin' => 'anonymous'
        ]);
    }

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

    /**
     * Returns a list of comment pagination links.
     * See paginate_links for the argument usage.
     * @param array $args
     * @return NavLink[]
     */
	private function getCommentPaginationLinks(array $args = []) {
		$prevNext = array_merge([
			'prev_text' => $this->wp->__('Previous'),
			'next_text' => $this->wp->__('Next')
		], $args);
		$args = array_merge($args, [
			'prev_text' => 'PREV',
			'next_text' => 'NEXT',
			'type' => '',
			'before_page_number' => '',
			'after_page_number' => '',
			'echo' => false
		]);

		$output = $this->wp->paginate_comments_links($args);
		$pLinks = [];
		if ($output) {
			$dom = new HTML5DOMDocument();
			$dom->loadHTML($output);
			$eBody = $dom->querySelector('body');
			foreach ($eBody->childNodes as $n) {
				if ($n->nodeType !== XML_ELEMENT_NODE) continue;
				$e = Type::DomElement($n);
				$text = $e->innerHTML;
				if ($e->tagName === 'a') {
					$href = $e->getAttribute('href');
					if ($text === 'PREV') {
						$pLinks[] = new NavLink(NavLink::PREV, $href, $prevNext['prev_text']);
					} else if ($text === 'NEXT') {
						$pLinks[] = new NavLink(NavLink::NEXT, $href, $prevNext['next_text']);
					} else {
						$pLinks[] = new NavLink(NavLink::PAGE, $href, $text);
					}
				} else if ($e->tagName === 'span') {
					$class = $e->getAttribute('class');
					if (strpos($class, 'dots') !== false) {
						$pLinks[] = new NavLink(NavLink::ELLIPSIS);
					} else if (strpos($class, 'current') !== false) {
						$pLinks[] = new NavLink(NavLink::CURRENT, null, $text);
					}
				}
			}
		}
		return $pLinks;
	}
}