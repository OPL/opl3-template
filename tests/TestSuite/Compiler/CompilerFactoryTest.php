<?php
/**
 * Unit tests for Open Power Template
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
namespace TestSuite\Compiler;
use Opl\Template\Compiler\CompilerFactory;

/**
 * @covers \Opl\Template\Compiler\CompilerFactory
 * @runTestsInSeparateProcesses
 */
class CompilerFactoryTest extends \PHPUnit_Framework_TestCase
{
	public function testAddLanguageAllowsToPassTheClassName()
	{
		$compilerFactory = new CompilerFactory();
		$this->assertEquals(array(), $compilerFactory->getLanguages());

		$compilerFactory->addLanguage('\Opl\Template\Compiler\Language\PHP\Language');
		$this->assertEquals(array('\Opl\Template\Compiler\Language\PHP\Language'), $compilerFactory->getLanguages());
	} // end testAddLanguageAllowsToPassTheClassName();
	
	public function testAddLanguageDoesNotValidateTheClassName()
	{
		$compilerFactory = new CompilerFactory();
		$this->assertEquals(array(), $compilerFactory->getLanguages());

		$compilerFactory->addLanguage('\Foo\Bar\Joe');
		$this->assertEquals(array('\Foo\Bar\Joe'), $compilerFactory->getLanguages());
	} // end testAddLanguageDoesNotValidateTheClassName();
	
	/**
	 * @expectedException Opl\Template\Exception\CompilerApiException
	 */
	public function testGetCompilerFailsOnUnexistingClass()
	{
		$compilerFactory = new CompilerFactory();
		$compilerFactory->addLanguage('\Foo\Bar\Joe');
		$compilerFactory->getCompiler();
	} // end testGetCompilerFailsOnUnexistingClass();
	
	/**
	 * @expectedException Opl\Template\Exception\CompilerApiException
	 */
	public function testGetCompilerFailsOnInvalidInterface()
	{
		$compilerFactory = new CompilerFactory();
		$compilerFactory->addLanguage('\Extra\CompilerFactoryTests\DummyClass');
		$compilerFactory->getCompiler();
	} // end testGetCompilerFailsOnInvalidInterface();
	
	public function testGetCompilerPassesOnValidClass()
	{
		$compilerFactory = new CompilerFactory();
		$compilerFactory->addLanguage('\Extra\CompilerFactoryTests\ValidClass');
		ob_start();
		$this->assertEquals('Opl\Template\Compiler\Compiler', get_class($compilerFactory->getCompiler()));
		$this->assertEquals('Called.', ob_get_clean());
	} // end testGetCompilerPassesOnValidClass();
	
	public function testGetCompilerReturnsTheSameCompilerWhenCalledTwice()
	{
		$compilerFactory = new CompilerFactory();
		$compilerFactory->addLanguage('\Extra\CompilerFactoryTests\ValidClass');
		ob_start();
		$compiler1 = $compilerFactory->getCompiler();
		
		$this->assertSame($compiler1, $compilerFactory->getCompiler());
		ob_end_clean();
	} // end testGetCompilerPassesOnValidClass();
} // end CompilerFactoryTest;