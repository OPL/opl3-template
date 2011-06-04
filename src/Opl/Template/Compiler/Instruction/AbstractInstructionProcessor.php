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
namespace Opl\Template\Compiler\Instruction;
use Opl\Template\Compiler\AST\Node;
use Opl\Template\Compiler\AST\Scannable;
use Opl\Template\Compiler\Compiler;
use SplQueue;

/**
 * Instruction processors are the pieces of code responsible for
 * performing certain manipulations on the Abstract Syntax Tree that
 * typically lead to produce some dynamic code or change the tree
 * structure. This class provides the foundation for writing them.
 * 
 * The abstract instruction processor does not put any constraints
 * of the kind of processing we are going to perform. This can be
 * done by implementing certain interfaces required by various compilation
 * stages.
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
abstract class AbstractInstructionProcessor
{
	/**
	 * The template compiler.
	 * @var Compiler
	 */
	protected $compiler;
	
	/**
	 * The children queue.
	 * @var SplQueue
	 */
	protected $queue;

	/**
	 * Sets the template compiler that manages this processor.
	 * 
	 * @param Compiler $compiler The template compiler.
	 * @return AbstractInstructionProcessor Fluent interface.
	 */
	public function setCompiler(Compiler $compiler)
	{
		$this->compiler = $compiler;
		return $this;
	} // end setCompiler();

	/**
	 * Returns the compiler.
	 * 
	 * @return Compiler 
	 */
	public function getCompiler()
	{
		return $this->compiler;
	} // end getCompiler();

	/**
	 * The instruction processor should use this method to free the memory
	 * once the template compilation is finished.
	 */
	public function dispose()
	{
		$this->compiler = null;
	} // end dispose();

	/**
	 * Allows the instruction processor to put the children of the given
	 * scannable into the processing queue.
	 * 
	 * @param Scannable $parent The parent whose children are enqueued.
	 * @return AbstractInstructionProcessor Fluent interface.
	 */
	public function enqueueChildren(Scannable $parent)
	{
		if($parent->hasChildren())
		{
			if(null === $this->queue)
			{
				$this->queue = new SplQueue();
			}
			foreach($parent as $child)
			{
				$this->queue->enqueue($child);
			}
		}
		return $this;
	} // end enqueueChildren();
	
	/**
	 * Allows the instruction processor to put a single child of the given
	 * scannable into the processing queue.
	 * 
	 * @param Node $node The node to enqueue.
	 * @return AbstractInstructionProcessor Fluent interface.
	 */
	public function enqueueChild(Node $node)
	{
		if(null === $this->queue)
		{
			$this->queue = new SplQueue();
		}
		$this->queue->enqueue($node);
		return $this;
	} // end enqueueChild();
	
	/**
	 * Returns the queue containing child nodes directed for processing
	 * by this processor. The queue is removed from the instruction processor.
	 * 
	 * @return SplQueue 
	 */
	final public function getEnqueuedChildren()
	{
		$queue = $this->queue;
		$this->queue = null;
		return $queue;
	} // end getEnqueuedChildren();
	
	/**
	 * The instruction processor should use this method to register itself
	 * in at least one compilation stage. The method is guaranteed to be
	 * called at the beginning of the compilation process.
	 */
	abstract public function configure();
} // end AbstractInstruction;