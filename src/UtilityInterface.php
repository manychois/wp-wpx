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
	function findAspectRatio(int $width, int $height) : string;
}
