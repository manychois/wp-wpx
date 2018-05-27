<?php
namespace Manychois\Wpx;

/**
 * Represents a single menu item.
 */
class MenuItem
{
	/**
	 * ID of nav_menu_item.
	 * @var int
	 */
	public $id;

	/**
	 * ID of the corresponding page / post / category.
	 * @var int
	 */
	public $objectId;

	/**
	 * The object type the link refers to, e.g. post, page, category.
	 * @var string
	 */
	public $objectType;

	/**
	 * The base type of the linked object. Either empty string, post, or taxonomy.
	 * @var string
	 */
	public $objectBaseType;

	/**
	 * Parent menu item.
	 * @var MenuItem
	 */
	public $parent;

	/**
	 * Child menu items.
	 * @var MenuItem[]
	 */
	public $children;

	/**
	 * Navigation label text.
	 * @var string
	 */
	public $label;

	/**
	 * Link title attribute value.
	 * @var string
	 */
	public $title;

	/**
	 * Link target attribute value.
	 * @var string
	 */
	public $target;

	/**
	 * CSS classes.
	 * @var string[]
	 */
	public $classes;

	/**
	 * Link relationship (XFN).
	 * @var string
	 */
	public $xfn;

	/**
	 * The description of the menu item.
	 * @var string
	 */
	public $description;

	/**
	 * Link URL.
	 * @var string
	 */
	public $url;

	/**
	 * Returns true if the menu item refers to the current page.
	 * @var bool
	 */
	public $isCurrent;

	/**
	 * Returns true if the menu item has a descendant item which refers to the current page.
	 * @var bool
	 */
	public $isCurrentParent;

	/**
	 * Level of depth.
	 * @var int
	 */
	public $depth;

	public function __construct()
	{
		$this->id = 0;
		$this->objectId = 0;
		$this->objectType = '';
		$this->objectBaseType = '';
		$this->children = [];
		$this->classes = [];
		$this->isCurrent = false;
		$this->isCurrentParent = false;
		$this->depth = 0;
	}
}