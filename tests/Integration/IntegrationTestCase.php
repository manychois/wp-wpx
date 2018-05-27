<?php
namespace Manychois\Wpx\Tests\Integration;

use Manychois\Wpx\WpContext;

class IntegrationTestCase extends \WP_UnitTestCase
{
	/**
	 * @return \Manychois\Wpx\WpContextInterface
	 */
	public function wp()
	{
		$wp = new WpContext();
		return $wp;
	}
}