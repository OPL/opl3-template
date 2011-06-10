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
namespace Opl\Template\Language\PHP\AST;
use Opl\Template\Compiler\AST\Node;

/**
 * Represents a static piece of code, either PHP or HTML.
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class Code extends Node
{
	const PHP_TYPE = 0;
	const PLAIN_TYPE = 1;
	
	/**
	 * The type of code stored in this node.
	 * @var integer
	 */
	protected $type;
	/**
	 * The code content.
	 * @var string
	 */
	protected $content;
	
	/**
	 * Constructs the node.
	 * 
	 * @param int $type The code block type
	 * @param string $content The code block content
	 */
	public function __construct($type, $content)
	{
		$this->type = (int) $type;
		$this->content = (string) $content;
	} // end __construct();
	
	/**
	 * Returns the code block type.
	 * 
	 * @return integer 
	 */
	public function getType()
	{
		return $this->type;
	} // end getType();
	
	/**
	 * Returns the code block content.
	 * 
	 * @return string 
	 */
	public function getContent()
	{
		return $this->content;
	} // end getContent();
} // end Code;