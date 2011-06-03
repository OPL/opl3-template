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
namespace Opl\Template\Compiler\Stage;
use Opl\Template\Compiler\CodeBufferCollection;
use Opl\Template\Compiler\AST\Attribute;
use Opl\Template\Compiler\AST\Cdata;
use Opl\Template\Compiler\AST\Comment;
use Opl\Template\Compiler\AST\Document;
use Opl\Template\Compiler\AST\Element;
use Opl\Template\Compiler\AST\Expression;
use Opl\Template\Compiler\AST\Node;
use Opl\Template\Compiler\AST\Text;
use Opl\Template\Compiler\AST\Scannable;
use Opl\Template\Compiler\Compiler;
use Opl\Template\Compiler\Expression\ExpressionInterface;
use Opl\Template\Compiler\Instruction\AbstractInstructionProcessor;
use Opl\Template\Compiler\Instruction\ProcessingStageInterface;
use Opl\Template\Compiler\PropertyCollection;
use Opl\Template\Exception\LinkerException;
use DomainException;
use SplQueue;
use SplStack;

/**
 * Performs compile-time manipulations on the AST. These instructions do not
 * emit any extra output code unless necessary, but rather focus on changing
 * the tree structure.
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class ManipulationStage implements StageInterface
{
	/**
	 * The compiler instance
	 * @var Opl\Template\Compiler\Compiler 
	 */
	private $compiler;
	/**
	 * The node property manager.
	 * @var NodePropertyManager
	 */
	private $nodePropertyManager;
	/**
	 * The map of the supported nodes to the visitor methods.
	 * @var array
	 */
	protected $supportedNodes = array(
		'Opl\Template\Compiler\AST\Element' => 'VisitElement',
		'Opl\Template\Compiler\AST\Document' => 'VisitDocument'
	);
	
	/**
	 * The elements registered by the instruction processors.
	 * @var array
	 */
	protected $registeredElements = array();
	
	/**
	 * The attributes registered by the instruction processors.
	 * @var array
	 */
	protected $registeredAttributes = array();
	
	/**
	 * @see StageInterface
	 */
	public function setCompiler(Compiler $compiler)
	{
		$this->compiler = $compiler;
		$this->codeBufferManager = $compiler->getCompiledUnit()->getCodeBufferManager();
		$this->nodePropertyManager = $compiler->getCompiledUnit()->getPropertyManager();
	} // end setCompiler();
	
	/**
	 * @see StageInterface
	 */
	public function process(Document $document)
	{
		$queue = new SplQueue;
		$stack = new SplStack;
		
		$queue->enqueue($document);
		while(true)
		{
			$item = $queue->dequeue();
			$newQueue = $this->processNode($item, 'pre');
			if(null !== $newQueue)
			{
				$stack->push(array($item, $queue));
				$queue = $newQueue;
			}
			else
			{
				// The node has not requested any subnodes to be processed,
				// so we post-process it immediately.
				$this->processNode($item, 'post');
			}
			// Close the current level, and execute post-processing for all the
			// nodes we are closing.
			while($queue->count() == 0)
			{
				if($stack->count() == 0)
				{
					break 2;
				}
				unset($queue);
				list($item, $queue) = $stack->pop();
				$this->processNode($item, 'post');
			}
		}
		return $document;
	} // end process();

	/**
	 * @see StageInterface
	 */
	public function dispose()
	{
		$this->compiler = null;
		$this->codeBufferManager = null;
		$this->nodePropertyManager = null;
	} // end dispose();
	
	/**
	 * Allows the instruction processor to register new elements within the given namespace
	 * that will be redirected to it during the processing phase. The instruction processor
	 * must implement the <tt>Opl\Template\Compiler\Instruction\ProcessingStageInterface</tt>
	 * interface.
	 * 
	 * @throws DomainException If the processor does not implement the necessary interface.
	 * @throws UnknownResourceException If the URI is not registered in the compiler.
	 * @param AbstractInstructionProcessor $processor The processor that wants to register something.
	 * @param string $namespaceUri The namespace URI, where the registered elements belong to.
	 * @param array $elements The list of elements to register.
	 * @return ProcessingStage Fluent interface.
	 */
	public function registerElements(AbstractInstructionProcessor $processor, $namespaceUri, array $elements)
	{
		if(!$processor instanceof ManipulationStageInterface)
		{
			throw new DomainException('Cannot register elements in the processing stage: the processor does not implement the ManipulationStageInterface.');
		}
		$uriId = $this->compiler->getURIIdentifier($namespaceUri);
		
		if(!isset($this->registeredElements[$uriId]))
		{
			$this->registeredElements[$uriId] = array();
		}
		foreach($elements as $element)
		{
			$element = (string)$element;
			$this->registeredElements[$uriId][$element] = $processor;
		}
		return $this;
	} // end registerElements();
	
	/**
	 * Allows the instruction processor to register new attributes within the given namespace
	 * that will be redirected to it during the processing phase. The instruction processor
	 * must implement the <tt>Opl\Template\Compiler\Instruction\ProcessingStageInterface</tt>
	 * interface.
	 * 
	 * @throws DomainException If the processor does not implement the necessary interface.
	 * @throws UnknownResourceException If the URI is not registered in the compiler.
	 * @param AbstractInstructionProcessor $processor The processor that wants to register something.
	 * @param string $namespaceUri The namespace URI, where the registered attributes belong to.
	 * @param array $elements The list of attributes to register.
	 * @return ProcessingStage Fluent interface.
	 */
	public function registerAttributes(AbstractInstructionProcessor $processor, $namespaceUri, array $attributes)
	{
		if(!$processor instanceof ManipulationStageInterface)
		{
			throw new DomainException('Cannot register elements in the processing stage: the processor does not implement the ManipulationStageInterface.');
		}
		$uriId = $this->compiler->getURIIdentifier($namespaceURI);
		
		if(!isset($this->registeredAttributes[$uriId]))
		{
			$this->registeredAttributes[$uriId] = array();
		}
		foreach($elements as $element)
		{
			$element = (string)$element;
			$this->registeredAttributes[$uriId][$element] = $processor;
		}
		return $this;
	} // end registerAttributes();
	
	/**
	 * This method delegates the execution to the method specializing in the
	 * concrete node type. Returns the SplQueue containing the sub-nodes or
	 * null, if no subnodes should be processed.
	 * 
	 * @param Node $node The node to process.
	 * @param string $preOrPost Pre- or post-processing?
	 * @return SplQueue
	 */
	protected function processNode(Node $node, $preOrPost)
	{
		$className = get_class($node);
		if(isset($this->supportedNodes[$className]))
		{
			$methodName = $preOrPost.$this->supportedNodes[$className];
			return $this->$methodName($node);
		}
		return null;
	} // end processNode();
	
	/**
	 * A Visitor pattern operation method for preprocessing the Element nodes.
	 * 
	 * @param Element $element The element to process.
	 */
	protected function preVisitElement(Element $element)
	{
		if(null !== ($id = $element->getURIIdentifier()))
		{
			// This element belongs to a special namespace.
			$name = $element->getName();
			if(isset($this->registeredElements[$id][$name]))
			{
				$processor = $this->registeredElements[$id][$name];
				$processor->processManipulationElement($element);
				return $processor->getEnqueuedChildren();
			}
			return null;			
		}
		else
		{
			$queue = null;
			if($element->hasAttributes())
			{
				$queue = $this->processAttributes($element);
			}
			if(null === $queue)
			{
				if($element->hasChildren())
				{
					$queue = new SplQueue();
					foreach($element as $child)
					{
						$queue->enqueue($child);
					}
				}
			}
			return $queue;
		}
	} // end preVisitElement();
	
	/**
	 * Performs the element attribute processing.
	 * 
	 * @param Element $element The currently processed element.
	 * @return SplQueue The sub-node processing queue.
	 */
	protected function processAttributes(Element $element)
	{
		foreach($element->getAttributes() as $attribute)
		{
			if(null !== ($id = $attribute->getURIIdentifier()))
			{
				// TODO: OPT instruction attribute recognition
			}
		}
		return null;
	} // end processAttributes();

	/**
	 * A Visitor pattern operation method for postprocessing the Element nodes.
	 * 
	 * @param Element $element The element to process.
	 */
	protected function postVisitElement(Element $element)
	{
		if(null !== ($id = $element->getURIIdentifier()))
		{
			$properties = $this->nodePropertyManager->getProperties($element);
			if($properties->get('postprocess') == true)
			{
				$name = $element->getName();
				if(isset($this->registeredElements[$id][$name]))
				{
					$processor = $this->registeredElements[$id][$name];
					$processor->postProcessManipulationElement($element);
				}
				$properties->set('postprocess', null); 
			}
		}
	} // end postVisitElement();

	/**
	 * A Visitor pattern operation method for preprocessing the Document nodes.
	 * 
	 * @param Document $element The document to process.
	 */
	protected function preVisitDocument(Document $element)
	{
		$element->setVisible(true);
		if($element->hasChildren())
		{
			$queue = new SplQueue();
			foreach($element as $child)
			{
				$queue->enqueue($child);
			}
			return $queue;
		}
		return null;
	} // end preVisitDocument();
	
	/**
	 * A Visitor pattern operation method for postprocessing the Document nodes.
	 * 
	 * @param Document $element The document to process.
	 */
	protected function postVisitDocument(Document $element)
	{

	} // end postVisitDocument();
} // end ManipulationStage;