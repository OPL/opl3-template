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
namespace Opl\Template\Compiler\Stage;
use Opl\Template\Compiler\Compiler;
use Opl\Template\Compiler\AST\Document;

/**
 * This interface defines the basic API for writing the compilation
 * stages without affecting anything else, yet still be able to talk
 * with the compiler.
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
interface StageInterface
{
	/**
	 * Allows to pass the compiler instance to the linker.
	 * 
	 * @param Compiler $compiler The compiler that uses this linker.
	 */
	public function setCompiler(Compiler $compiler);
	
	/**
	 * Performs some operations on the AST typical to this processing stage.
	 * 
	 * @throws Opl\Template\Exception\StageException
	 * @param Opl\Template\Compiler\AST\Document $document The document to process.
	 * @return string
	 */
	public function process(Document $document);
	
	/**
	 * Clears the internal references in order to free the memory.
	 */
	public function dispose();
} // end StageInterface;