<?php
/*
 *  OPEN POWER LIBS <http://www.invenzzia.org>
 *
 * This file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE. It is also available through
 * WWW at this URL: <http://www.invenzzia.org/license/new-bsd>
 *
 * Copyright (c) Invenzzia Group <http://www.invenzzia.org>
 * and other contributors. See website for details.
 */
namespace Opl\Template\Language\PHP;
use Opl\Template\Compiler\Compiler;
use Opl\Template\Compiler\LanguageInterface;

/**
 * This piece of code allows to write templates directly in the PHP language.
 * However, because we have a fully-featured template compiler, we can do
 * something special. The PHP template language implementation provides the
 * support for a macro preprocessor that allows to use template inheritance
 * and compile-time macros in order to speed up the template execution.
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class Language implements LanguageInterface
{
	/**
	 * @see LanguageInterface
	 */
	public function initializeLanguage(Compiler $compiler)
	{
		if($compiler->getParser() !== null)
		{
			throw new LanguageException('Cannot initialize PHP: another template language is selected.');
		}
		
		$compiler->setParser($parser = new PHPParser());
		$compiler->setLinker(new PHPLinker());
	} // end initializeLanguage();
} // end Language;