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
namespace Opl\Template\Compiler\Linker;
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
use Opl\Template\Compiler\PropertyCollection;
use Opl\Template\Exception\LinkerException;
use SplQueue;
use SplStack;

/**
 * Links the Abstract Syntax Tree into a valid PHP file which serves as a compiled
 * version of the template. The class implements the Visitor design pattern to
 * process the AST nodes.
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class XmlLinker implements LinkerInterface
{
	/**
	 * The compiler instance
	 * @var Opl\Template\Compiler\Compiler 
	 */
	private $compiler;
	
	/**
	 * The current template output.
	 * @var string
	 */
	private $output = '';
	
	/**
	 * The code buffer manager.
	 * @var NodePropertyManager
	 */
	private $codeBufferManager;
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
		'Opl\Template\Compiler\AST\Expression' => 'VisitExpression',
		'Opl\Template\Compiler\AST\Text' => 'VisitText',
		'Opl\Template\Compiler\AST\Cdata' => 'VisitCdata',
		'Opl\Template\Compiler\AST\Comment' => 'VisitComment',
		'Opl\Template\Compiler\AST\Document' => 'VisitDocument'
	);
	
	/**
	 * @see Opl\Template\Compiler\Linker\LinkerInterface
	 */
	public function setCompiler(Compiler $compiler)
	{
		$this->compiler = $compiler;
		$this->codeBufferManager = $compiler->getCompiledUnit()->getCodeBufferManager();
		$this->nodePropertyManager = $compiler->getCompiledUnit()->getPropertyManager();
	} // end setCompiler();
	
	/**
	 * @see Opl\Template\Compiler\Linker\LinkerInterface
	 */
	public function dispose()
	{
		$this->compiler = null;
		$this->codeBufferManager = null;
		$this->nodePropertyManager = null;
	} // end dispose();
	
	/**
	 * @see Opl\Template\Compiler\Linker\LinkerInterface
	 */
	public function link(Document $document)
	{
		$queue = new SplQueue;
		$stack = new SplStack;

		$queue->enqueue($document);
		while(true)
		{
			$item = $queue->dequeue();
			if($item->isVisible())
			{
				$newQueue = $this->processNode($item, 'pre');
				if(null !== $newQueue)
				{
					$stack->push(array($item, $queue));
					$queue = $newQueue;
				}
				else
				{
					// The node has not requested any subnodes to be processed,
					// so we post-link it immediately.
					$this->processNode($item, 'post');
				}
			}
			// Close the current level, and execute post-linking for all the
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
		$output = $this->output;
		$this->output = '';
		return $output;
	} // end link();
	
	/**
	 * @todo Implement.
	 * @see Opl\Template\Compiler\Linker\LinkerInterface
	 */
	public function hasDynamicBlocks()
	{
		return false;
	} // end hasDynamicBlocks();
	
	/**
	 * @todo Implement.
	 * @see Opl\Template\Compiler\Linker\LinkerInterface
	 */
	public function getDynamicBlocks()
	{
		return array();
	} // end getDynamicBlocks();
	
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
		$elementCodeBuffers = $this->codeBufferManager->getProperties($element);
		$elementProperties = $this->nodePropertyManager->getProperties($element);
		
		if($element->getURIIdentifier() !== null)
		{
			// Special namespace elements
			if(!$element->hasChildren() && $element->isEmpty())
			{
				$this->output .= $elementCodeBuffers->link(array(CodeBufferCollection::TAG_BEFORE, CodeBufferCollection::TAG_SINGLE_BEFORE,
					CodeBufferCollection::TAG_SINGLE_AFTER, CodeBufferCollection::TAG_AFTER));
				return null;
			}
			else
			{
				$this->output .= $elementCodeBuffers->link(array(CodeBufferCollection::TAG_BEFORE, CodeBufferCollection::TAG_OPENING_BEFORE,
					CodeBufferCollection::TAG_OPENING_AFTER, CodeBufferCollection::TAG_CONTENT_BEFORE));
				return $this->enqueue($element);
			}
		}
		else
		{
			// Ordinary elements are rewritten
			if($elementCodeBuffers->hasContent(CodeBufferCollection::TAG_NAME))
			{
				$name = $elementCodeBuffers->link(array(CodeBufferCollection::TAG_NAME));
			}
			else
			{
				$name = $element->getFullyQualifiedName();
			}

			if(!$element->hasChildren() && $element->isEmpty())
			{
				$this->output .= $elementCodeBuffers->link(array(CodeBufferCollection::TAG_BEFORE, CodeBufferCollection::TAG_SINGLE_BEFORE));
				$this->output .= '<'.$name.$this->linkAttributes($element, $elementCodeBuffers, $elementProperties).' />';
				$this->output .= $elementCodeBuffers->link(array(CodeBufferCollection::TAG_SINGLE_AFTER, CodeBufferCollection::TAG_AFTER));
				return null;
			}
			else
			{
				$this->output .= $elementCodeBuffers->link(array(CodeBufferCollection::TAG_BEFORE, CodeBufferCollection::TAG_OPENING_BEFORE));
				$this->output .= '<'.$name.$this->linkAttributes($element, $elementCodeBuffers, $elementProperties).'>';
				$this->output .= $elementCodeBuffers->link(array(CodeBufferCollection::TAG_OPENING_AFTER, CodeBufferCollection::TAG_CONTENT_BEFORE));

				$elementProperties->set('link:name', $name);
				return $this->enqueue($element);
			}
		}
	} // end preVisitElement();
	
	/**
	 * A Visitor pattern operation method for postprocessing the Element nodes.
	 * 
	 * @param Element $element The element to process.
	 */
	protected function postVisitElement(Element $element)
	{
		if($element->hasChildren() || !$element->isEmpty())
		{
			$elementProperties = $this->nodePropertyManager->getProperties($element);
			$elementCodeBuffers = $this->codeBufferManager->getProperties($element);
			
			if($element->getURIIdentifier() === null)
			{
				$this->output .= $elementCodeBuffers->link(array(CodeBufferCollection::TAG_CONTENT_AFTER, CodeBufferCollection::TAG_CLOSING_BEFORE));
				$this->output .= '</'.$elementProperties->get('link:name').'>';
				$this->output .= $elementCodeBuffers->link(array(CodeBufferCollection::TAG_CLOSING_AFTER, CodeBufferCollection::TAG_AFTER));
			}
			else
			{
				$this->output .= $elementCodeBuffers->link(array(CodeBufferCollection::TAG_CONTENT_AFTER, CodeBufferCollection::TAG_CLOSING_BEFORE,
					CodeBufferCollection::TAG_CLOSING_AFTER, CodeBufferCollection::TAG_AFTER));
			}
		}
	} // end postVisitElement();
	
	/**
	 * An utility method with the algorithm for linking XML attributes back
	 * into the valid tags. Returns the linked attribute code.
	 * 
	 * @param Element $element The element whose attributes must be linked
	 * @param CodeBufferCollection $elementCodeBuffers The element code buffer collection
	 * @param PropertyCollection $elementProperties The element property collection
	 * @return string
	 */
	protected function linkAttributes(Element $element, CodeBufferCollection $elementCodeBuffers, PropertyCollection $elementProperties)
	{
		if($element->hasAttributes() || $elementCodeBuffers->hasContent(CodeBufferCollection::TAG_BEGINNING_ATTRIBUTES) || $elementCodeBuffers->hasContent(CodeBufferCollection::TAG_ENDING_ATTRIBUTES))
		{
			$code = $elementCodeBuffers->link(array(CodeBufferCollection::TAG_ATTRIBUTES_BEFORE, CodeBufferCollection::TAG_BEGINNING_ATTRIBUTES));
			foreach($element->getAttributes() as $attribute)
			{
				// Skip OPT namespace
				// TODO: Write
				
				// Linking
				$code .= ' '.$attribute->getFullyQualifiedName().'="'.htmlspecialchars($attribute->getValue()).'"';
			}
			$code .= $elementCodeBuffers->link(array(CodeBufferCollection::TAG_ENDING_ATTRIBUTES, CodeBufferCollection::TAG_ATTRIBUTES_AFTER));
			return $code;
		}
	} // end linkAttributes();
	
	/**
	 * A Visitor pattern operation method for preprocessing the Text nodes.
	 * 
	 * @param Text $element The text to process.
	 */
	protected function preVisitText(Text $element)
	{
		$elementCodeBuffers = $this->codeBufferManager->getProperties($element);
		$this->output .= $elementCodeBuffers->link(array(CodeBufferCollection::TAG_BEFORE));
		return $this->enqueue($element);
	} // end preVisitText();
	
	/**
	 * A Visitor pattern operation method for postprocessing the Text nodes.
	 * 
	 * @param Text $element The text to process.
	 */
	protected function postVisitText(Text $element)
	{
		$elementCodeBuffers = $this->codeBufferManager->getProperties($element);
		$this->output .= $elementCodeBuffers->link(array(CodeBufferCollection::TAG_AFTER));
	} // end postVisitText();
	
	/**
	 * A Visitor pattern operation method for preprocessing the Document nodes.
	 * 
	 * @param Document $element The document to process.
	 */
	protected function preVisitDocument(Document $element)
	{
		$elementCodeBuffers = $this->codeBufferManager->getProperties($element);
		$this->output .= $elementCodeBuffers->link(array(CodeBufferCollection::TAG_BEFORE));
		return $this->enqueue($element);
	} // end preVisitDocument();
	
	/**
	 * A Visitor pattern operation method for postprocessing the Document nodes.
	 * 
	 * @param Document $element The document to process.
	 */
	protected function postVisitDocument(Document $element)
	{
		$elementCodeBuffers = $this->codeBufferManager->getProperties($element);
		$this->output .= $elementCodeBuffers->link(array(CodeBufferCollection::TAG_AFTER));
	} // end postVisitDocument();
	
	/**
	 * A Visitor pattern operation method for preprocessing the Cdata nodes.
	 * 
	 * @param Cdata $element The CDATA to process.
	 */
	protected function preVisitCdata(Cdata $element)
	{
		$elementCodeBuffers = $this->codeBufferManager->getProperties($element);
		$this->output .= $elementCodeBuffers->link(array(CodeBufferCollection::TAG_BEFORE));
		
		$this->output .= (string)$element;
	} // end preVisitCdata();
	
	/**
	 * A Visitor pattern operation method for postprocessing the Cdata nodes.
	 * 
	 * @param Cdata $element The CDATA to process.
	 */
	protected function postVisitCdata(Cdata $element)
	{
		$elementCodeBuffers = $this->codeBufferManager->getProperties($element);
		$this->output .= $elementCodeBuffers->link(array(CodeBufferCollection::TAG_AFTER));
	} // end postVisitCdata();
	
	/**
	 * A Visitor pattern operation method for preprocessing the Expression nodes.
	 * 
	 * @param Expression $element The expression to process.
	 */
	protected function preVisitExpression(Expression $element)
	{
		$this->output .= $this->codeBufferManager->getProperties($element)->link(array(CodeBufferCollection::TAG_CONTENT));
	} // end preVisitExpression();
	
	/**
	 * A Visitor pattern operation method for preprocessing the Expression nodes.
	 * 
	 * @param Expression $element The expression to process.
	 */
	protected function postVisitExpression(Expression $element)
	{
		
	} // end postVisitExpression();
	
	/**
	 * Produces a new processing queue from the children of the given element.
	 * 
	 * @param Scannable $scannable The scannable node.
	 * @return SplQueue
	 */
	protected function enqueue(Scannable $scannable)
	{
		if(!$scannable->hasChildren())
		{
			return null;
		}
		$item = $scannable->getFirstChild();
		$queue = new SplQueue;
		while(null !== $item)
		{
			$queue->enqueue($item);
			$item = $item->getNext();
		}
		return $queue;
	} // end enqueue();
} // end XmlLinker;