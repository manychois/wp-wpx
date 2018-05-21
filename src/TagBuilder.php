<?php
namespace Manychois\Wpx;

/**
 * A string builder for HTML tag.
 */
class TagBuilder
{
	/**
	 * @var string
	 */
	private $tagName;
	/**
	 * @var array
	 */
	private $attrs;
	/**
	 * @var array
	 */
	private $inners;

	/**
	 * Creates a tag builder which outputs HTML tag.
	 * @param string $tagName Node name of the element.
	 */
	public function __construct(string $tagName)
	{
		$this->tagName = $tagName;
		$this->attrs = [];
		$this->inners = [];
	}

	/**
	 * Insert contents to the end of the element.
	 * @param string|TagBuilder $contents One or more child elements to insert.
	 * @return TagBuilder Returns the instance itself.
	 */
	public function append(... $contents) : TagBuilder
	{
		$this->inners = array_merge($this->inners, $contents);
		return $this;
	}

	/**
	 * Returns the attribute value of the specified name.
	 * @param string $name The attribute name.
	 * @return null|string Returns the attribute value, or null if the attribute does not exist.
	 */
	public function getAttr(string $name)
	{
		if (array_key_exists($name, $this->attrs))
			return $this->attrs[$name];
		else
			return null;
	}

	/**
	 * Set or override the attribute values. You have to escape the attribute value if required.
	 * @param array $attrs
	 * @return TagBuilder Returns the instance itself.
	 */
	public function setAttr(array $attrs) : TagBuilder
	{
		foreach ($attrs as $k => $v) {
			if (is_numeric($k)) {
				$this->attrs[$v] = '';
			} else {
				$this->attrs[$k] = $v;
			}
		}
		return $this;
	}

	public function __toString() {
		$html = '<' . $this->tagName;
		if ($this->attrs) {
			foreach ($this->attrs as $k => $v) {
				if ($v === '') {
					$html .= " $k";
				} else {
					$html .= " $k=\"$v\"";
				}
			}
		}
		if ($this->inners) {
			$html .= '>';
			foreach ($this->inners as $item) {
				$html .= $item;
			}
			$html .= "</{$this->tagName}>";
		} else {
			$html .= ' />';
		}
		return $html;
	}
}