<?php
namespace Manychois\Wpx;

/**
 * Gives type hint to IDE, no action is done.
 */
class Type
{
	/**
	 * @return \IvoPetkov\HTML5DOMElement
	 */
	public static function DomElement($node) {
		return $node;
	}
}