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
use Iterator;

/**
 * This iterator allows to iterate through the Scannable objects. Note that if
 * we perform some manipulations on the iterated Scannable object at the same
 * time, the results produced by this iterator might be unpredictable. In order
 * to ensure the correctness in case of any manipulations, please obtain the
 * children list as an array.
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class ASTIterator implements Iterator
{
	/**
	 * The initial iteration node.
	 * @var Node
	 */
	protected $initialNode;
	/**
	 * The current node.
	 * @var Node
	 */
	protected $current;
	/**
	 * The current node ordinal number.
	 * @var integer
	 */
	protected $key;
	
	/**
	 * Creates the iterator.
	 * 
	 * @param Node $initialNode The initial node.
	 */
	public function __construct(Node $initialNode = null)
	{
		$this->initialNode = $initialNode;
		$this->current = $initialNode;
		$this->key = 0;
	} // end __construct();
	
	/**
	 * Rewinds the iterator back to the initial node.
	 */
	public function rewind()
	{
		$this->current = $this->initialNode;
		$this->key = 0;
	} // end reset();
	
	/**
	 * Returns the node ordinal number.
	 * @return integer
	 */
	public function key()
	{
		return $this->key;
	} // end key();
	
	/**
	 * Returns the current node.
	 * @return Node
	 */
	public function current()
	{
		return $this->current;
	} // end current();
	
	/**
	 * Moves forward to the next node.
	 */
	public function next()
	{
		$this->current = $this->current->getNext();
		$this->key++;
	} // end next();
	
	/**
	 * Checks if we observe a valid node.
	 * @return boolean
	 */
	public function valid()
	{
		return null !== $this->current;
	} // end valid();
} // end ASTIterator;
