<?php
/**
 * Unit tests for Open Power Template
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
namespace TestSuite;
use Opl\Template\Context;
use stdClass;

/**
 * @covers \Opl\Template\Context
 * @runTestsInSeparateProcesses
 */
class ContextTest extends \PHPUnit_Framework_TestCase
{
	public function testSettingAndGettingTemplateVariables()
	{
		$context = new Context();
		
		$context->setVar('foo', 'bar');
		$this->assertEquals('bar', $context->getVar('foo'));
		
		$this->assertSame(null, $context->getVar('joe'));
	} // end testSettingAndGettingTemplateVariables();
	
	public function testMassVarSetting()
	{
		$context = new Context();
		
		$context->setVars(array(
			'foo' => 'bar',
			'joe' => 'goo',
			'moo' => 'hoo'
		));
		$this->assertEquals('bar', $context->getVar('foo'));
		$this->assertEquals('goo', $context->getVar('joe'));
		$this->assertEquals('hoo', $context->getVar('moo'));
		
		$this->assertSame(null, $context->getVar('loo'));
	} // end testMassVarSetting();
	
	public function testSettingAndGettingObjects()
	{
		$context = new Context();
		
		$obj = new stdClass();
		
		$context->setObject('foo', $obj);
		$this->assertSame($obj, $context->getObject('foo'));
		$this->assertSame(null, $context->getObject('joe'));
	} // end testSettingAndGettingObjects();
	
	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testSetObjectThrowsAnExceptionIfNoObjectIsPassed()
	{
		$context = new Context();
		$context->setObject('foo', 'scalar value');
	} // end testSetObjectThrowsAnExceptionIfNoObjectIsPassed();
} // end ContextTest;