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
use Opl\Template\Unit;

require_once 'vfsStream/vfsStream.php';

/**
 * @covers \Opl\Template\Unit
 * @runTestsInSeparateProcesses
 */
class UnitTest extends \PHPUnit_Framework_TestCase
{
	public function testConstructorSetsTheTemplateName()
	{
		$unit = new Unit('foo.tpl');
		$this->assertEquals('foo.tpl', $unit->getTemplateName());
	} // end testConstructorSetsTheTemplateName();
	
	public function testSettingGlobalContext()
	{
		$unit = new Unit('foo.tpl');
		$globalCtx = new Context();
		
		$this->assertSame(null, $unit->getGlobalContext());
		$unit->setGlobalContext($globalCtx);
		$this->assertSame($globalCtx, $unit->getGlobalContext());
	} // end testSettingGlobalContext();
	
	public function testDebugExecutionExecutesTheTemplateAndCompilesIt()
	{
		$inflector = $this->getMock('Opl\Template\Inflector\InflectorInterface');
		$inflector->expects($this->once())
			->method('getSourcePath')
			->with($this->equalTo('foo.tpl'))
			->will($this->returnValue('./data/UnitTest/foo.tpl'));
		$inflector->expects($this->once())
			->method('getCompiledPath')
			->with($this->equalTo('foo.tpl'), $this->equalTo(array()))
			->will($this->returnValue('./data/UnitTest/foo.php'));
		
		// TODO: PhpUnit-mock-objects has a bug with identicalTo(), because
		// the mock objects actually clone some of the arguments which causes
		// comparing objects in the argument to fail.
		$compiler = $this->getMock('Opl\Template\Compiler\Compiler');
		$compiler->expects($this->once())
			->method('compile')
			->with($this->equalTo('./data/UnitTest/foo.tpl'), $this->equalTo('./data/UnitTest/foo.php'), $this->equalTo($inflector));
		
		$compilerFactory = $this->getMock('Opl\Template\Compiler\CompilerFactoryInterface');
		$compilerFactory->expects($this->once())
			->method('getCompiler')
			->will($this->returnValue($compiler));
		
		$unit = new Unit('foo.tpl');
		ob_start();
		$unit->executeDebug($inflector, $compilerFactory);
		$text = ob_get_clean();
		$this->assertEquals('Just a dummy file.', $text);
	} // end testDebugExecutionExecutesTheTemplateAndCompilesIt();
} // end UnitTest;