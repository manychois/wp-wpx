<?php
namespace Manychois\Wpx\Tests;
/**
 * An dummy implementation of WpContextInterface for unit testing purpose.
 */
class WpContext implements \Manychois\Wpx\WpContextInterface
{
	private $hooks = [];

	public function addHook(string $name, callable $hook)
	{
		$this->hooks[$name] = $hook;
	}

	/**
	 * @param string $name
	 * @return \callable|null
	 */
	private function getHook(string $name)
	{
		if (key_exists($name, $this->hooks)) {
			return $this->hooks[$name];
		} else {
			return null;
		}
	}

	#region Manychois\Wpx\WpContextInterface Members

	/**
	 * Hook a function or method to a specific filter action.
	 *
	 * WordPress offers filter hooks to allow plugins to modify
	 * various types of internal data at runtime.
	 *
	 * A plugin can modify data by binding a callback to a filter hook. When the filter
	 * is later applied, each bound callback is run in order of priority, and given
	 * the opportunity to modify a value by returning a new value.
	 *
	 * The following example shows how a callback function is bound to a filter hook.
	 *
	 * Note that `$example` is passed to the callback, (maybe) modified, then returned:
	 *
	 *     function example_callback( $example ) {
	 *         // Maybe modify $example in some way.
	 *         return $example;
	 *     }
	 *     add_filter( 'example_filter', 'example_callback' );
	 *
	 * Bound callbacks can accept from none to the total number of arguments passed as parameters
	 * in the corresponding apply_filters() call.
	 *
	 * In other words, if an apply_filters() call passes four total arguments, callbacks bound to
	 * it can accept none (the same as 1) of the arguments or up to four. The important part is that
	 * the `$accepted_args` value must reflect the number of arguments the bound callback *actually*
	 * opted to accept. If no arguments were accepted by the callback that is considered to be the
	 * same as accepting 1 argument. For example:
	 *
	 *     // Filter call.
	 *     $value = apply_filters( 'hook', $value, $arg2, $arg3 );
	 *
	 *     // Accepting zero/one arguments.
	 *     function example_callback() {
	 *         ...
	 *         return 'some value';
	 *     }
	 *     add_filter( 'hook', 'example_callback' ); // Where $priority is default 10, $accepted_args is default 1.
	 *
	 *     // Accepting two arguments (three possible).
	 *     function example_callback( $value, $arg2 ) {
	 *         ...
	 *         return $maybe_modified_value;
	 *     }
	 *     add_filter( 'hook', 'example_callback', 10, 2 ); // Where $priority is 10, $accepted_args is 2.
	 *
	 * *Note:* The function will return true whether or not the callback is valid.
	 * It is up to you to take care. This is done for optimization purposes, so
	 * everything is as quick as possible.
	 *
	 * @since 0.71
	 *
	 * @global array $wp_filter      A multidimensional array of all hooks and the callbacks hooked to them.
	 *
	 * @param string   $tag             The name of the filter to hook the $function_to_add callback to.
	 * @param callable $function_to_add The callback to be run when the filter is applied.
	 * @param int      $priority        Optional. Used to specify the order in which the functions
	 *                                  associated with a particular action are executed. Default 10.
	 *                                  Lower numbers correspond with earlier execution,
	 *                                  and functions with the same priority are executed
	 *                                  in the order in which they were added to the action.
	 * @param int      $accepted_args   Optional. The number of arguments the function accepts. Default 1.
	 * @return true
	 */
	function add_filter($tag, $function_to_add, $priority = 10, $accepted_args = 1)
	{
		$callback = $this->getHook('add_filter');
		if ($callback) {
			return call_user_func($callback, $tag, $function_to_add, $priority, $accepted_args);
		}
		return true;
	}

	/**
	 * Removes a function from a specified action hook.
	 *
	 * This function removes a function attached to a specified action hook. This
	 * method can be used to remove default functions attached to a specific filter
	 * hook and possibly replace them with a substitute.
	 *
	 * @since 1.2.0
	 *
	 * @param string   $tag                The action hook to which the function to be removed is hooked.
	 * @param callable $function_to_remove The name of the function which should be removed.
	 * @param int      $priority           Optional. The priority of the function. Default 10.
	 * @return bool Whether the function is removed.
	 */
	public function remove_action($tag, $function_to_remove, $priority = 10)
	{
		$callback = $this->getHook('remove_action');
		if ($callback) {
			return call_user_func($callback, $tag, $function_to_remove, $priority);
		}
		return true;
	}

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
		return '';
	}

	#endregion
}