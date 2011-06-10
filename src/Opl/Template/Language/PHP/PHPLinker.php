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
namespace Opl\Template\Language\PHP;
use Opl\Template\Compiler\AST\Document;
use Opl\Template\Compiler\AST\Node;
use Opl\Template\Compiler\AST\Scannable;
use Opl\Template\Compiler\Compiler;
use Opl\Template\Compiler\Linker\LinkerInterface;
use Opl\Template\Language\PHP\AST\Code;
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
class PHPLinker implements LinkerInterface
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
	 * The node property manager.
	 * @var NodePropertyManager
	 */
	private $nodePropertyManager;
	
	/**
	 * The map of the supported nodes to the visitor methods.
	 * @var array
	 */
	protected $supportedNodes = array(
		'Opl\Template\Language\PHP\AST\Code' => 'VisitCode',
		'Opl\Template\Compiler\AST\Document' => 'VisitDocument'
	);
	
	/**
	 * @see Opl\Template\Compiler\Linker\LinkerInterface
	 */
	public function setCompiler(Compiler $compiler)
	{
		$this->compiler = $compiler;
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
	 * A Visitor pattern operation method for preprocessing the Document nodes.
	 * 
	 * @param Document $element The document to process.
	 */
	protected function preVisitDocument(Document $element)
	{
		$this->output .= '<?php extract($this->data, EXTR_SKIP); extract($this->objects, EXTR_SKIP); ?>';
		return $this->enqueue($element);
	} // end preVisitDocument();
	
	/**
	 * A Visitor pattern operation method for postprocessing the Document nodes.
	 * 
	 * @param Document $element The document to process.
	 */
	protected function postVisitDocument(Document $element)
	{
		/* null */
	} // end postVisitDocument();
	
	/**
	 * A Visitor pattern operation method for preprocessing the Code nodes.
	 * 
	 * @param Document $element The document to process.
	 */
	protected function preVisitCode(Code $element)
	{
		if($element->getType() == Code::PLAIN_TYPE)
		{
			$this->output .= $element->getContent();
		}
		else
		{
			$this->output .= '<?php'.$element->getContent().'?>';
		}
	} // end preVisitCode();
	
	/**
	 * A Visitor pattern operation method for postprocessing the Code nodes.
	 * 
	 * @param Document $element The document to process.
	 */
	protected function postVisitCode(Code $element)
	{
		/* null */
	} // end postVisitCode();
	
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
} // end PHPLinker;