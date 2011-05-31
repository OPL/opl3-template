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
namespace Opl\Template\Compiler\Linker;
use Opl\Template\Compiler\Compiler;
use Opl\Template\Compiler\AST\Document;

/**
 * This interface allows to write new AST linkers that produce the actual
 * output.
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
interface LinkerInterface
{
	/**
	 * Allows to pass the compiler instance to the linker.
	 * 
	 * @param Compiler $compiler The compiler that uses this linker.
	 */
	public function setCompiler(Compiler $compiler);
	
	/**
	 * Parses the code from the given file and produces an abstract syntax
	 * tree. Opening the file and reading its contents is the responsibility
	 * of the method, because it might allow a more efficient memory usage
	 * than reading the source and passing it as a string.
	 * 
	 * @throws Opl\Template\Exception\LinkerException
	 * @param Opl\Template\Compiler\AST\Document $document The document to link.
	 * @return string
	 */
	public function link(Document $document);
	
	/**
	 * Returns true, if the template defines some pieces of code that should
	 * remain dynamic even in case of caching.
	 * 
	 * @return boolean
	 */
	public function hasDynamicBlocks();
	
	/**
	 * Returns the array of codes for the pieces of code that should remain
	 * dynamic even in case of caching.
	 * 
	 * @return array
	 */
	public function getDynamicBlocks();
	
	/**
	 * Clears the internal references in order to free the memory.
	 */
	public function dispose();
} // end LinkerInterface;
