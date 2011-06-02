<?php
/**
 * Unit tests for Open Power Template
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
namespace TestSuite\Compiler;
use Opl\Template\Compiler\PropertyCollection;

/**
 * @covers \Opl\Template\Compiler\PropertyCollection
 * @runTestsInSeparateProcesses
 */
class PropertyCollectionTest extends \PHPUnit_Framework_TestCase
{
	public function testSetterAndGetter()
	{
		$collection = new PropertyCollection();
		$collection->set('foo', 'bar');
		$this->assertEquals('bar', $collection->get('foo'));
	} // end testSetterAndGetter();
	
	public function testGetReturnsNullIfElementDoesNotExist()
	{
		$collection = new PropertyCollection();
		$collection->set('foo', 'bar');
		$this->assertEquals('bar', $collection->get('foo'));
		$this->assertNull($collection->get('joe'));
	} // end testGetReturnsNullIfElementDoesNotExist();
	
	public function testDisposeClearsMemory()
	{
		$collection = new PropertyCollection();
		$collection->set('foo', 'bar');
		$this->assertEquals('bar', $collection->get('foo'));
		
		$collection->dispose();
		$this->assertNull($collection->get('foo'));
	} // end testDisposeClearsMemory();
} // end PropertyCollectionTest;