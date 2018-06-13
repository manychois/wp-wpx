<?php
namespace Manychois\Wpx;
/**
 * Represents a comment author.
 */
class CommentAuthor
{
	/**
	 * Author name.
	 * @var string
	 */
	public $name;
	/**
	 * Author email.
	 * @var string
	 */
	public $email;
	/**
	 * Author website.
	 * @var string
	 */
	public $url;
	/**
	 * IP address of the author.
	 * @var string
	 */
	public $ip;
	/**
	 * Author avatar HTML.
	 * @var string
	 */
	public $avatarHtml;
}