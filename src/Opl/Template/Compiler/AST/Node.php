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
 * The base node class for the Abstract Syntax Tree used by the
 * compiler.
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
abstract class Node
{
	/**
	 * The parent of the specified node.
	 */
	private $parent;
	/**
	 * The previous node at the same level.
	 */
	private $previous;
	/**
	 * The next node at the same level.
	 */
	private $next;
	
	/**
	 * Is the node visible in the final output?
	 * @var boolean
	 */
	private $visible = false;
	
	/**
	 * Sets the node parent. Implements fluent interface.
	 *
	 * @param Node parent The parent node.
	 * @return The self-reference.
	 */
	public function setParent(Node $parent = null)
	{
		$this->parent = $parent;
		return $this;
	} // end setParent();
	
	/**
	 * Returns the parent of the given node.
	 *
	 * @return The node parent.
	 */
	public function getParent()
	{
		return $this->parent;
	} // end getParent();
	
	public function setPrevious(Node $node = null)
	{
		$this->previous = $node;
		return $this;
	} // end setPrevious();
	
	public function getPrevious()
	{
		return $this->previous;
	} // end getPrevious();

	public function setNext(Node $node = null)
	{
		$this->next = $node;
		return $this;
	} // end setNext();
	
	public function getNext()
	{
		return $this->next;
	} // end getNext();
	
	public function __clone()
	{
		$this->parent = null;
		$this->previous = null;
		$this->next = null;
	} // end __clone();
	
	/**
	 * Unmounts the node from the current parent. Implements
	 * fluent interface.
	 * 
	 * @throws CompilerException
	 * @return Fluent interface
	 */
	public function unmount()
	{
		if(null !== $this->parent)
		{
			$this->parent->removeChild($this);
		}
		return $this;
	} // end unmount();
	
	/**
	 * Sets the new node visibility status.
	 * 
	 * @param boolean $visibility The new visibility status.
	 * @return Node Fluent interface.
	 */
	public function setVisible($visibility)
	{
		$this->visible = (bool)$visibility;
		return $this;
	} // end setVisible();
	
	/**
	 * Returns true, if the node is visible.
	 * 
	 * @return boolean
	 */
	public function isVisible()
	{
		return $this->visible;
	} // end isVisible();
} // end Node;