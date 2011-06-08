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
namespace Opl\Template\Compiler;

/**
 * Encapsulates different template languages and their extension
 * sets. These languages can be loaded with a compiler factory
 * during the compiler initialization.
 * 
 * A class implementing this interface should configure the compiler
 * to handle the specified language, by setting the parser, linker,
 * required compilation stages and instructions.
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
interface LanguageInterface
{
	/**
	 * Configures the compiler to handle the specified template language.
	 * This method might be used either for languages or their extensions.
	 * In the second case, it should only check whether the compiler has
	 * the proper parser, linker and compilation stages set.
	 * 
	 * @throws LanguageException
	 * @param Compiler $compiler The compiler to configure.
	 */
	public function initializeLanguage(Compiler $compiler);
} // end LanguageInterface;