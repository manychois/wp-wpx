<?php
namespace Manychois\Wpx\Tests;
/**
 * An dummy implementation of WpContextInterface for unit testing purpose.
 */
class WpContext implements \Manychois\Wpx\WpContextInterface
{
	private $hooks;

	public function addHook(string $name, callable $hook)
	{
		$this->hooks[$name] = $hook;
	}

	private function getHook(string $name) : callable
	{
		if (key_exists($name, $this->hooks)) {
			return $this->hooks[$name];
		} else {
			return null;
		}
	}

	#region Manychois\Wpx\WpContextInterface Members

	/**
	 * Navigates through an array, object, or scalar, and removes slashes from the values.
	 *
	 * @param mixed $value The value to be stripped.
	 *
	 * @return mixed Stripped value.
	 */
	public function stripslashes_deep($value)
	{
		$callback = $this->getHook('stripslashes_deep');
		if ($callback) {
			return call_user_func($callback, $value);
		}
		return null;
	}

	#endregion
}