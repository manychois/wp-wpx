<?php
namespace Manychois\Wpx\Tests;

use Manychois\Wpx\TagBuilder;

class TagBuilderTest extends UnitTestCase
{
	public function test_append()
	{
		$tb = new TagBuilder('a');
		$tb->append('It ');

		$strong = new TagBuilder('strong');
		$strong->append('works');

		$tb->append($strong, '!');

		$this->assertSame('<a>It <strong>works</strong>!</a>', $tb->__toString());
	}

	public function test_getSetAttr()
	{
		$tb = new TagBuilder('a');
		$tb->setAttr(['href' => '#', 'data-toggle', 'target' => '_blank']);
		$this->assertSame('#', $tb->getAttr('href'));
		$this->assertSame('_blank', $tb->getAttr('target'));
		$this->assertSame('', $tb->getAttr('data-toggle'));
		$this->assertNull($tb->getAttr('ref'));
	}

	public function test_toString()
	{
		$tb = new TagBuilder('div');
		$this->assertSame('<div />', $tb->__toString());
		$tb->setAttr(array('hidden', 'class' => 'none'));
		$this->assertSame('<div hidden class="none" />', $tb->__toString());
	}
}