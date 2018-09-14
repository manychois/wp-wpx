<?php
namespace Manychois\Wpx\Tests;

use PHPUnit\Framework\TestCase;

abstract class UnitTestCase extends TestCase
{
	/**
	 * @return \Manychois\Wpx\WpContextInterface
	 */
	public function wp()
	{
		$wp = $this->createMock(\Manychois\Wpx\WpContextInterface::class);
		return $wp;
	}
}