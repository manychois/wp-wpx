<?php
namespace Manychois\Wpx;

/**
 * Represents a pagination link.
 */
class NavLink
{
	const PAGE = 0;
	const PREV = 1;
	const NEXT = 2;
	const CURRENT = 3;
	const ELLIPSIS = 4;

	/**
	 * Constrcut a pagination link.
	 * @param int $type One of PAGE, PREV, NEXT, CURRENT, ELLIPSIS
	 * @param string $href
	 * @param string $text
	 */
	function __construct(int $type, string $href = null, string $text = null)
	{
		$this->type = $type;
		$this->href = $href;
		$this->text = $text;
	}

	/**
	 * The type of the pagination link.
	 * @var int
	 */
	public $type;

	/**
	 * The link URL.
	 * @var string
	 */
	public $href;

	/**
	 * The page number, or the post title.
	 * @var string
	 */
	public $text;
}