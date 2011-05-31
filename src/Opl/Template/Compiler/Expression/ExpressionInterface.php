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
namespace Opl\Template\Compiler\Expression;
use Opl\Template\Compiler\Compiler;

/**
 * This interface allows to write new expression engines responsible for
 * compiling expressions.
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
interface ExpressionInterface
{
	/**
	 * Indicates that this expression is an assignment at the top level.
	 */
	const EXPR_ASSIGNMENT = 0;
	/**
	 * The compiled expression contains just a scalar value.
	 */
	const EXPR_SCALAR = 1;
	/**
	 * There is nothing special about this expression.
	 */
	const EXPR_DEFAULT = 2;
	
	/**
	 * Allows to pass the compiler instance to the expression engine.
	 * 
	 * @param Compiler $compiler The compiler that uses this expression engine.
	 */
	public function setCompiler(Compiler $compiler);
	
	/**
	 * Parses the given expression into a compiled code. The product of
	 * the method is a triple containing three elements:
	 * 
	 *  - bare compiled expression
	 *  - escaped compiled expression
	 *  - the expression type (see the interface constants)
	 * 
	 * @throws Opl\Template\Exception\ExpressionException
	 * @param string $filename The file name with the source code.
	 * @return array
	 */
	public function parse($expression);
	
	/**
	 * Clears the internal references in order to free the memory.
	 */
	public function dispose();
} // end ParserInterface;