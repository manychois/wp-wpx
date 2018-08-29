<?php
namespace Manychois\Wpx;

/**
 * Gives type hints to IDE, no operation is performed.
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