<?php
namespace Manychois\Wpx;

/**
 * A utility library for overriding WordPress default HTML output easily.
 */
class Utility implements UtilityInterface
{

	#region Manychois\Wpx\UtilityInterface Members

	/**
	 * Return an approximate aspect ratio based on the width and height provided.
	 * Return empty if no common aspect ratio is matched.
	 * Supported ratios: 1x1, 4x3, 16x9, 21x9.
	 * @param int $width
	 * @param int $height
	 * @return string The closest aspect ratio to the specified width and height.
	 */
	function findAspectRatio(int $width, int $height) : string
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
	 * Safe get the value from $_GET. The value is stripped to undo WordPress default slash insertion.
	 * @param string $name
	 * @param mixed $default Value when the name is not found. Default is null.
	 * @return mixed
	 */
	public function getFromGet(string $name, $default = null)
	{
		if (isset($_GET[$name])) {
			return stripslashes_deep($_GET[$name]); // TODO: Remove WordPress dependency
		} else {
			return $default;
		}
	}

	#endregion
}