<?php
/**
 * Unit tests for Open Power Template
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
namespace TestSuite;
use Opl\Template\Unit;

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
} // end UnitTest;