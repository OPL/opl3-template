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
 * A dummy expression engine that treats the expression just as a PHP string.
 * Its purpose is simplifying string insertion.
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class StringExpression implements ExpressionInterface
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
		$expression = addslashes($expression);
		return array('htmlspecialchars(\''.$expression.'\')', '\''.$expression.'\'', ExpressionInterface::EXPR_SCALAR);
	} // end parse();
} // end StringExpression;