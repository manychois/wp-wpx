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
	 * @param int $width
	 * @param int $height
	 * @return string The closest aspect ratio to the specified width and height.
	 */
	public function findAspectRatio(int $width, int $height) : string;

	/**
	 * Safe get the value from $_GET. The value is stripped to undo WordPress default slash insertion.
	 * @param string $name
	 * @param mixed $default Value when the name is not found. Default is null.
	 * @return mixed
	 */
	public function getFromGet(string $name, $default = null);

	/**
	 * Safe get the value from $_POST. The value is stripped to undo WordPress default slash insertion.
	 * @param string $name
	 * @param mixed $default Value when the name is not found. Default is null.
	 * @return mixed
	 */
	public function getFromPost(string $name, $default = null);

	/**
	 * Reduce unnecessary WordPress default stuff in <head> tag.
	 * @param array $args
	 *     Optional. Array of arguments.
	 *     "api"   bool Set true to remove WP REST API link tag. Default true.
	 *     "emoji" bool Set true to remove emoji related style and javascript. Default true.
	 */
	public function minimizeHead(array $args = []);

	/**
	 * Initialize a tag builder.
	 * @param string $tagName Node name of the element.
	 * @return TagBuilder Returns tag builder with tag name initialized.
	 */
	public function newTag(string $tagName) : TagBuilder;

	/**
	 * Register a new style.
	 * @param string $handle Name of the stylesheet. Should be unique.
	 * @param array $attrs Associative array of HTML atrributes of the style link tag. Attribute href must be present.
	 * @param array $deps Optional. An array of registered stylesheet handles this stylesheet depends on. Default empty array.
	 * @return void
	 */
	public function registerStyle(string $handle, array $attrs, array $deps = array());
}
