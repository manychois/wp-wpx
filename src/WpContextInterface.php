<?php
namespace Manychois\Wpx;
/**
 * Used to isolate direct access to WordPress built-in functions and global variables.
 */
interface WpContextInterface
{
    /**
	 * Navigates through an array, object, or scalar, and removes slashes from the values.
	 *
	 * @since 2.0.0
	 *
	 * @param mixed $value The value to be stripped.
	 * @return mixed Stripped value.
	 */
	public function stripslashes_deep($value);
}
