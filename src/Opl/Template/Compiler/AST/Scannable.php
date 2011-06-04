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
use Countable;
use IteratorAggregate;
use SplQueue;

/**
 * This class implements the API to manage the tree structure. The API is very
 * similar to the Document Object Model interface, but it is modified to handle
 * the template Abstract Syntax Trees properly.
 *
 * The nodes that implement this interface can append, insert, remove and
 * replace the child nodes, or in other words - they cannot be leaves.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class Scannable extends Node implements Countable, IteratorAggregate
{
	private $firstChild;
	private $lastChild;
	private $size = 0;
	private $cloneMarker = 1;
	
	/**
	 * Appends a new child to the end of the node children list. If the child
	 * type does not match the allowed types to be kept by the node, an exception
	 * is thrown.
	 *
	 * If the appended child was referenced somewhere else, it is removed from
	 * the previous location before mounting.
	 *
	 * Implements fluent interface.
	 * 
	 * @param Node $child The child to append. 
	 * @return Scannable Fluent interface.
	 * @throws CompilerException The child type is not allowed here.
	 */
	public function appendChild(Node $child)
	{
		$this->isChildTypeAllowed($child);
		
		$child->unmount();
		
		if(null === $this->lastChild)
		{
			$this->firstChild = $this->lastChild = $child;
		}
		else
		{
			$child->setPrevious($this->lastChild);
			$this->lastChild->setNext($child);
			$this->lastChild = $child;
		}
		$child->setParent($this);
		$this->size++;
		
		return $this;
	} // end appendChild();
	
	/**
	 * Inserts a new child before the specified referenced node. The referenced
	 * node must be a valid child of the current node; otherwise an exception
	 * is thrown. If the reference node is null, the method works excactly as
	 * appendChild(). The reference node may be also specified as an ordinal
	 * number.
	 *
	 * If the inserted child was referenced somewhere else, it is removed
	 * from the previous location before mounting.
	 *
	 * Implements fluent interface.
	 * 
	 * @param Node $child The child to insert.
	 * @param Node|integer $refNode The reference node or the reference node number.
	 * @return Scannable Fluent interface.
	 * @throws CompilerException
	 */
	public function insertBefore(Node $child, $refNode)
	{
		if(!is_object($refNode) || !$refNode instanceof Node)
		{
			$refNode = $this->findReferenceNodeByIndex((int) $refNode, 'insertBefore');
		}
		
		if(null === $refNode)
		{
			return $this->appendChild($child);
		}
		$this->isChildTypeAllowed($child);
		if($refNode->getParent() !== $this)
		{
			throw new ASTException('Cannot perform insertBefore(): the reference node parent does not match the calling node.');
		}
		$child->unmount();
		
		if($refNode->getPrevious() !== null)
		{
			$refNode->getPrevious()->setNext($child);
			$child->setPrevious($refNode->getPrevious());
		}
		
		$child->setNext($refNode);
		$child->setParent($this);
		$refNode->setPrevious($child);
		
		if($refNode === $this->firstChild)
		{
			$this->firstChild = $child;
		}

		$this->size++;
		return $this;
	} // end insertBefore();
	
	/**
	 * Removes a child from the current node. The child can be either identified
	 * by an object or by an ordinal number. If the child does not exist or is
	 * not a valid child, an exception is thrown.
	 * 
	 * Implements fluent interface.
	 * 
	 * @param Node|integer $node The node to remove.
	 * @return Scannable Fluent interface.
	 * @throws ASTException
	 */
	public function removeChild($node)
	{
		if(!is_object($node) || !$node instanceof Node)
		{
			$node = $this->findReferenceNodeByIndex((int) $refNode, 'removeChild');
		}
		if($node->getParent() !== $this)
		{
			throw new ASTException('Cannot perform removeChild(): the node parent does not match the calling node.');
		}
		// The border cases...
		$next = $node->getNext();
		$prev = $node->getPrevious();
		if($this->firstChild === $node)
		{
			$this->firstChild = $next;
		}
		if($this->lastChild === $node)
		{
			$this->lastChild = $prev;
		}
		// Unlink it.
		if(null !== $prev)
		{
			$prev->setNext($next);
		}
		if(null !== $next)
		{
			$next->setPrevious($prev);
		}
		$node->setParent(null)->setPrevious(null)->setNext(null);
		$this->size--;
		return $this;		
	} // end removeChild();
	
	/**
	 * Removes all the children from the current node and returns them as
	 * an array.
	 * 
	 * @return array The array containing the removed children.
	 */
	public function removeChildren()
	{
		$removedNodes = array();
		$scan = $this->firstChild;
		$next = null;
		while(null !== $scan)
		{
			$next = $scan->getNext();
			$scan->setParent(null)->setPrevious(null)->setNext(null);
			$removedNodes[] = $scan;
			$scan = $next;
		}
		$this->firstChild = $this->lastChild = null;
		$this->size = 0;
		return $removedNodes;
	} // end removeChildren();
	
	/**
	 * Replaces the given child reference node with a new node. The reference
	 * node can be identified either by passing the node object itself, or
	 * by the ordinal number.
	 * 
	 * Implements fluent interface.
	 * 
	 * @param Node $newNode The new node to insert.
	 * @param Node|int $refNode The replaced node.
	 * @return Node Fluent interface.
	 * @throws ASTException
	 */
	public function replaceChild(Node $newNode, $refNode)
	{
		if(!is_object($refNode) || !$refNode instanceof Node)
		{
			$refNode = $this->findReferenceNodeByIndex((int) $refNode, 'replaceChild');
		}
		if($refNode->getParent() !== $this)
		{
			throw new ASTException('Cannot perform replaceChild(): the node parent does not match the calling node.');
		}
		$this->isChildTypeAllowed($newNode);
		
		// Now, do the replacement.
		$newNode->unmount();
		
		$newNode->setPrevious($prev = $refNode->getPrevious())
			->setNext($next = $refNode->getNext())
			->setParent($this);
		
		if(null === $prev)
		{
			$this->firstChild = $newNode;
		}
		if(null === $next)
		{
			$this->lastChild = $newNode;
		}
		$refNode->setParent(null)->setPrevious(null)->setNext(null);
		return $this;		
	} // end replaceChild();
	
	/**
	 * Moves all the children to a different node. The destination node
	 * must be empty, otherwise an exception is thrown.
	 * 
	 * Implements fluent interface.
	 * 
	 * @param Scannable $destinationNode The destination node.
	 * @return Node Fluent interface.
	 * @throws ASTException
	 */
	public function moveChildren(Scannable $destinationNode)
	{
		return $this;
	} // end moveChildren();
	
	/**
	 * Returns true, if the node has any children.
	 * @return boolean 
	 */
	public function hasChildren()
	{
		return $this->size > 0;
	} // end hasChildren();
	
	/**
	 * Returns the number of children in the given node.
	 * @return int
	 */
	public function count()
	{
		return $this->size;
	} // end count();
	
	/**
	 * Returns the first child of the given node.
	 * @return Node
	 */
	public function getFirstChild()
	{
		return $this->firstChild;
	} // end getFirstChild();
	
	/**
	 * Returns the last child of the given node.
	 * @return Node
	 */
	public function getLastChild()
	{
		return $this->lastChild;
	} // end getLastChild();
	
	/**
	 * Constructs and returns the Scannable object iterator. Note that you should
	 * not perform any simultaneous manipulations on the tree structure while scanning
	 * the contents of the node, because it may produce unpredictable results. In
	 * such a case, consider using the <tt>getChildren()</tt> method.
	 * 
	 * @return ASTIterator 
	 */
	public function getIterator()
	{
		return new ASTIterator($this->firstChild);
	} // end getIterator();
	
	/**
	 * Returns an array containing all the children.
	 * 
	 * @return array
	 */
	public function getChildren()
	{
		$children = array();
		$item = $this->firstChild;
		while(null !== $item)
		{
			$children[] = $item;
			$item = $item->getNext();
		}
		return $children;
	} // end getChildren();
	
	/**
	 * An utility method that finds the reference node by its ordinal number
	 * within the children list. If the child does not exist, an exception
	 * is thrown.
	 * 
	 * @param int $refNodeIdx The reference node number.
	 * @param string $whoUses The name of the method that calls us.
	 * @return Node
	 * @throws ASTException If the child does not exist.
	 */
	protected function findReferenceNodeByIndex($refNodeIdx, $whoUses)
	{
		$i = 0;
		$scan = $this->firstChild;
		while(null !== $scan)
		{
			if($i == $refNodeIdx)
			{
				return $scan;
			}
			$i++;
			$scan = $scan->getNext();
		}
		throw new ASTException('Cannot perform '.$whoUses.'(): the child with the position '.$refNodeIdx.' does not exist.');
	} // end findReferenceNodeByIndex();
	
	/**
	 * This method should be overwritten by the child classes and implement checking,
	 * whether the given node can be accepted. If there is a problem, an exception
	 * should be thrown.
	 * 
	 * @param Node $node The node to check.
	 * @throws ASTException If the node is not allowed.
	 */
	protected function isChildTypeAllowed(Node $node)
	{
		/* null */
	} // end isChildTypeAllowed();
	
	public function __clone()
	{
		parent::__clone();
		
		if($this->cloneMarker == 1)
		{
			$queue = new SplQueue();
			$item = $this->firstChild;
			while(null !== $item)
			{
				$queue->enqueue(array($this, $item));
				$item = $item->getNext();
			}
			$this->firstChild = null;
			$this->lastChild = null;
			$this->size = 0;
			while($queue->count() > 0)
			{
				list($parent, $item) = $queue->dequeue();
				if($item instanceof Scannable)
				{
					$item->cloneMarker = 0;
					
					$children = $item->getChildren();					
					$clonedItem = clone $item;
					foreach($children as $child)
					{
						$queue->enqueue(array($clonedItem, $child));
					}
				}
				else
				{
					$clonedItem = clone $item;
				}
				$parent->appendChild($clonedItem);
			}
		}
		else
		{
			$this->firstChild = null;
			$this->lastChild = null;
			$this->size = 0;
			$this->cloneMarker = 1;
		}
	} // end __clone();
	
	/**
	 * Clears all the references between the nodes, so that the tree can
	 * be easily flushed out of the memory by the garbage collector.
	 */
	public function dispose()
	{
		$this->unmount();
		
		$queue = new SplQueue;
		foreach($this->getChildren() as $item)
		{
			$queue->enqueue($item);
		}
		$everything = array();
		while($queue->count() > 0)
		{
			$item = $queue->dequeue();
			if($item instanceof Scannable)
			{
				foreach($item->getChildren() as $child)
				{
					$queue->enqueue($child);
				}
			}
			$everything[] = $item;
		}
		
		foreach($everything as $item)
		{
			$item->setParent(null)->setPrevious(null)->setNext(null);
		}
	} // end dispose();
} // end Scannable;