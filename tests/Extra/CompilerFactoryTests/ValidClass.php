<?php
/**
 * Additional files required for Open Power Template testing.
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
namespace Extra\CompilerFactoryTests;
use Opl\Template\Compiler\Compiler;
use Opl\Template\Compiler\LanguageInterface;

/**
 * For Compiler Factory - the class that does not implement the required interface.
 */
class ValidClass implements LanguageInterface
{
	public function initializeLanguage(Compiler $compiler)
	{
		echo 'Called.';
	} // end initializeLanguage();
} // end DummyClass;