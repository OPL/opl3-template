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
namespace Opl\Template\Language\Declari\Expression;
use Opl\Template\Compiler\Compiler;
use Opl\Template\Compiler\Expression\ExpressionInterface;

/**
 * The parser and compiler for the Declari Expression Language.
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class DeclariExpression implements ExpressionInterface
{
	/**
	 * @see ExpressionInterface
	 */
	public function setCompiler(Compiler $compiler)
	{
		/* null */
	} // end setCompiler();

	/**
	 * @see ExpressionInterface
	 */
	public function dispose()
	{
		/* null */
	} // end dispose();
	
	/**
	 * @see ExpressionInterface
	 */
	public function parse($expression)
	{
		// Currently, there is just a dummy implementation. It will be extended into
		// a complete LALR(1) parser.
		
		if($expression[0] == '$')
		{
			$expression = '$this->data[\''.ltrim($expression, '$').'\']';
		}

		return array($expression, $expression, ExpressionInterface::EXPR_DEFAULT);
	} // end parse();
} // end DeclariExpression;