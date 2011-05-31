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
namespace Opl\Template\Exception;

/**
 * These exceptions are reported by the expression engines.
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class ExpressionException extends CompilationException
{
	/**
	 * The failing expression.
	 * @var string 
	 */
	protected $failingExpression;
	
	/**
	 * Sets the expression that failed to compile.
	 * 
	 * @param string $expression 
	 * @return ExpressionException
	 */
	public function setFailingExpression($expression)
	{
		$this->failingExpression = $expression;
		return $this;
	} // end setFailingExpression();
	
	/**
	 * Returns the expression that failed.
	 * 
	 * @return string
	 */
	public function getFailingExpression()
	{
		return $this->failingExpression;
	} // end getFailingExpression();
} // end ExpressionException;