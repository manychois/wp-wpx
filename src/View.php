<?php
namespace Manychois\Wpx;

/**
 * Represents a view which can be nested to form a structured output hierarchy.
 */
abstract class View
{
	/**
	 * @var View
	 */
	private $parent;
	/**
	 * @var View
	 */
	private $child;

	/**
	 * Model is supplied during the rendering process.
	 * @var mixed
	 */
	protected $model;

	/**
	 * Echoes the view, rednering from topmost view to the bottom one.
	 * @return void
	 */
	public final function render($model = null) {
		$sequence = [$this];
		while (true) {
			$parent = $sequence[0]->parent;
			if ($parent) {
				array_unshift($sequence, $parent);
			} else {
				break;
			}
		}
		for ($i = 0; $i < count($sequence); ++$i) {
			$sequence[$i]->model = $model;
		}
		$sequence[0]->content();
	}

	/**
	 * Implements this to define how this view should be rendered.
	 * @return void
	 */
	protected abstract function content();

	/**
	 * Calls this to render the child view, if any.
	 * @return void
	 */
	protected final function body() {
		if ($this->hasBody()) {
			$this->child->content();
		}
	}

	/**
	 * Checks if a child view exists.
	 * @return boolean Returns true if a child view exists.
	 */
	protected final function hasBody() : bool {
		return !is_null($this->child);
	}

	/**
	 * Checks if a child view has the function "section_$name" defined.
	 * @param string $name Name of the section.
	 * @return boolean Returns true of the function "section_$name" defined in the child view.
	 */
	protected final function hasSection(string $name) : bool {
		return $this->hasBody() && method_exists($this->child, "section_$name");
	}

	/**
	 * Calls this to render the section defined in the child view.
	 * @param string $name Name of the section.
	 * @return void
	 */
	protected final function section(string $name) {
		if ($this->hasSection($name)) {
			call_user_func(array($this->child, "section_$name"));
		}
	}

	/**
	 * Calls this to assign a parent view. The parent view will has this instance set as child view.
	 * @param View $parent Parent view.
	 * @return void
	 */
	protected final function setParentView(View $parent) {
		$this->parent = $parent;
		if ($parent) {
			$parent->child = $this;
		}
		$this->child = null;
	}
}