<?php
namespace Manychois\Wpx;

class Comment
{
	/**
	 * Comment Id. Topmost comment will have this value as 0.
	 * @var int
	 */
	public $id;
	/**
	 * Parent comment.
	 * @var null|Comment
	 */
	public $parent;
	/**
	 * Child comments.
	 * @var Comment[]
	 */
	public $children;
	/**
	 * Whether the comment has been approved.
	 * @var bool
	 */
	public $isApproved;
	/**
	 * Comment content.
	 * @var string
	 */
	public $content;
	/**
	 * Comment author.
	 * @var CommentAuthor
	 */
	public $author;
	/**
	 * Comment local time.
	 * @var string
	 */
	public $time;
	/**
	 * Comment UTC time.
	 * @var string
	 */
	public $timeUtc;
	/**
	 * Depth of comment.
	 * @var int
	 */
	public $depth;

	public function __construct()
	{
		$this->id = 0;
		$this->depth = 0;
		$this->parent = null;
		$this->children = [];
	}
}