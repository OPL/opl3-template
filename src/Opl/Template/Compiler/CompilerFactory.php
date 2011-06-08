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
 * This is the standard implementation of the compiler factory provided
 * with OPT. It allows to specify the list of template languages and their
 * extensions that we want to use with the compiler to process our
 * languages.
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class CompilerFactory implements CompilerFactoryInterface
{
	/**
	 * The language storage.
	 * @var array 
	 */
	protected $languages = array();
	
	/**
	 * The constructed compiler.
	 * @var Compiler
	 */
	protected $compiler = null;
	
	/**
	 * Registers the name of the class implementing the <tt>\Opl\Template\Compiler\LanguageInterface</tt>.
	 * The languages are initialized in the order specified by this method calls.
	 * 
	 * @param string $className The template language class name.
	 * @return CompilerFactory Fluent interface.
	 */
	public function addLanguage($className)
	{
		$this->languages[] = (string)$className;
	} // end addLanguage();
	
	/**
	 * Returns an array containing the names of all the languages initialized
	 * by this factory.
	 * 
	 * @return array
	 */
	public function getLanguages()
	{
		return $this->languages;
	} // end getLanguages();
	
	/**
	 * @see CompilerFactoryInterface
	 */
	public function getCompiler()
	{
		if(null !== $this->compiler)
		{
			return $this->compiler;
		}
		
		$compiler = new Compiler();
		
		foreach($this->languages as $className)
		{
			if(!class_exists($className))
			{
				throw new CompilerApiException('Cannot find the template language: \''.$className.'\'.');
			}
			$object = new $className();
			
			if(!$object instanceof LanguageInterface)
			{
				throw new CompilerApiException('The template language object does not implement the required interface \\Opl\\Template\\Compiler\\LanguageInterface.');
			}
			
			$object->initializeLanguage($compiler);
		}
		return $this->compiler = $compiler;
	} // end getCompiler();
} // end CompilerFactory;