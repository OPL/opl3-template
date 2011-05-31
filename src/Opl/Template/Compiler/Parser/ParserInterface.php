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
namespace Opl\Template\Compiler\Parser;
use Opl\Template\Compiler\Compiler;

/**
 * This interface allows to write new source code parsers for the
 * compiler.
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
interface ParserInterface
{
	/**
	 * Allows to pass the compiler instance to the parser.
	 * 
	 * @param Compiler $compiler The compiler that uses this parser.
	 */
	public function setCompiler(Compiler $compiler);
	
	/**
	 * Parses the code from the given file and produces an abstract syntax
	 * tree. Opening the file and reading its contents is the responsibility
	 * of the method, because it might allow a more efficient memory usage
	 * than reading the source and passing it as a string.
	 * 
	 * @throws Opl\Template\Exception\FilesystemException
	 * @throws Opl\Template\Exception\ParserException
	 * @param string $filename The file name with the source code.
	 * @return Opl\Template\Compiler\AST\Document
	 */
	public function parse($filename);
	
	/**
	 * Clears the internal references in order to free the memory.
	 */
	public function dispose();
} // end ParserInterface;