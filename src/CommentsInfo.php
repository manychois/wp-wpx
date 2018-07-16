<?php
namespace Manychois\Wpx;

/**
 * Contains information of comments of a post/ page.
 */
class CommentsInfo
{
	/**
	 * Number of comments.
	 * @var int
	 */
	public $commentCount;
    /**
     * Comment form.
     * @var null|CommentForm
     */
    public $commentForm;
	/**
	 * Whether the post is open for comments.
	 * @var mixed
	 */
	public $isCommentAllowed;
	/**
	 * Whether this post type supports comment feature.
	 * @var mixed
	 */
	public $isCommentSupported;
	/**
	 * Whether the post is password protected.
	 * If it is true, comments should not be shown.
	 * @var bool
	 */
	public $isPasswordRequired;

	/**
	 * Topmost comment.
	 * @var Comment
	 */
	public $topComment;

	/**
	 * Comment pagination links.
	 * @var NavLink[]
	 */
	public $paginationLinks;

	public function __construct()
	{
		$this->commentCount = 0;
        $this->commentForm = null;
		$this->isCommentAllowed = false;
		$this->isCommentSupported = false;
		$this->isPasswordRequired = false;
		$this->topComment = new Comment();
		$this->paginationLinks = [];
	}
}