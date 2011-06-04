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
use Opl\Template\Exception\ASTException;

/**
 * Represents an ordinary text between markup elements. The text may
 * consist of Character Data nodes and embedded expressions.
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class Text extends Scannable
{	
	/**
	 * This method is a syntactic sugar which simplifies appending the text.
	 * If the last node is an expression, a new CDATA node is started. Otherwise,
	 * the text is appended to the existing CDATA node.
	 * 
	 * @param string $text The text to append.
	 */
	public function appendData($text)
	{
		$last = $this->getLastChild();
		if(null === $last || !$last instanceof Cdata)
		{
			$this->appendChild(new Cdata($text));
		}
		else
		{
			$last->appendData($text);
		}
	} // end appendData();
	
	/**
	 * Tests if the node contains only the whitespace symbols.
	 * 
	 * @return boolean
	 */
	public function isWhitespace()
	{
		$item = $this->getFirstChild();
		while(null !== $item)
		{
			if($item instanceof Cdata)
			{
				if(!ctype_space((string)$item))
				{
					return false;
				}
			}
			else
			{
				return false;
			}
			$item = $item->getNext();
		}
		return true;
	} // end isWhitespace();
	
	/**
	 * @see Opl\Template\Compiler\AST\Scannable
	 */
	protected function isChildTypeAllowed(Node $node)
	{
		if(!$node instanceof Cdata && !$node instanceof Expression)
		{
			throw new ASTException('Invalid node type for the Text node: Cdata or Expression nodes allowed.');
		}
	} // end isChildTypeAllowed();
	
	public function __toString()
	{
		return 'TEXT';
	} // end __toString();
} // end Text;