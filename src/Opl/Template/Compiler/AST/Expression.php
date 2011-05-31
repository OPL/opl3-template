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
namespace Opl\Template\Compiler\AST;

/**
 * The node represents an embedded expression.
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class Expression extends Node
{
	/**
	 * @var string 
	 */
	private $expression;
	
	/**
	 * @var string 
	 */
	private $type;
	
	/**
	 * Initializes the node with the given expression and its type. The type
	 * identifies the parser that will be responsible for producing the PHP
	 * code from it.
	 * 
	 * @param string $expression The expression content
	 * @param string $type The expression type
	 */
	public function __construct($expression, $type)
	{
		$this->expression = (string)$expression;
		$this->type = (string)$type;
	} // end __construct();
	
	/**
	 * Returns the expression.
	 * 
	 * @return string 
	 */
	public function getExpression()
	{
		return $this->expression;
	} // end getExpression();
	
	/**
	 * Returns the expression type.
	 * 
	 * @return string 
	 */
	public function getType()
	{
		return $this->type;
	} // end getType();
} // end Expression;