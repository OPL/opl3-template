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
namespace Opl\Template\Compiler;
use Opl\Template\Compiler\Expression\ExpressionInterface;
use Opl\Template\Compiler\Instruction\AbstractInstructionProcessor;
use Opl\Template\Compiler\Linker\LinkerInterface;
use Opl\Template\Compiler\Parser\ParserInterface;
use Opl\Template\Compiler\Stage\StageInterface;
use Opl\Template\Exception\UnknownResourceException;
use Opl\Template\Inflector\InflectorInterface;
use Opl\Template\Unit;
use SplQueue;
use SplStack;

/**
 * The compiler class is responsible for managing the compilation process. The
 * compiled unit-specific data are delegated to other objects, so the compiler
 * does not depend on them.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class Compiler
{
	/**
	 * The compiled unit contains the current compilation-specific stuff.
	 * @var CompiledUnit
	 */
	protected $compiledUnit;
	/**
	 * The parser used to build the AST.
	 * @var ParserInterface
	 */
	protected $parser;
	/**
	 * Different compilation stages performing some operations on AST.
	 * @var array
	 */
	protected $stages = array();
	/**
	 * The linker that emits the output code
	 * @var LinkerInterface
	 */
	protected $linker;
	/**
	 * The list of namespace URIs redirected to the instruction processors.
	 * @var array
	 */
	protected $namespaceURI = array();
	
	/**
	 * The reversed namespace URI mapper.
	 * @var array 
	 */
	protected $reverseURIMapper = array();
	
	/**
	 * The instructions registered in the compiler.
	 * @var array 
	 */
	protected $instructions = array();
	
	/**
	 * The list of the expression engines available during the compilation.
	 * @var array
	 */
	protected $expressionEngines = array();

	public function setCompiledUnit(CompiledUnit $unit)
	{
		$this->compiledUnit = $unit;
		return $this;
	} // end setCompiledUnit();

	/**
	 * Returns the compiled unit which stores the compilation-specific
	 * data.
	 * 
	 * @return CompiledUnit 
	 */
	public function getCompiledUnit()
	{
		return $this->compiledUnit;
	} // end getCompiledUnit();
	
	/**
	 * Sets the source code parser.
	 * 
	 * @param ParserInterface $parser The parser used by the compiler.
	 * @return Compiler Fluent interface.
	 */
	public function setParser(ParserInterface $parser)
	{
		$this->parser = $parser;
		return $this;
	} // end setParser();
	
	/**
	 * Returns the parser used by this compiler
	 * 
	 * @return ParserInterface
	 */
	public function getParser()
	{
		return $this->parser;
	} // end getParser();
	
	/**
	 * Sets the Abstract Syntax Tree linker.
	 * 
	 * @param LinkerInterface $linker The new linker.
	 * @return Compiler Fluent interface.
	 */
	public function setLinker(LinkerInterface $linker)
	{
		$this->linker = $linker;
		return $this;
	} // end setLinker();
	
	/**
	 * Returns the AST linker used by this compiler.
	 * 
	 * @return Fluent interface. 
	 */
	public function getLinker()
	{
		return $this->linker;
	} // end getLinker();
	
	/**
	 * Registers a new processing stage within the compiler under the given name.
	 * 
	 * @param string $name The processing stage name
	 * @param StageInterface $stage The processing stage.
	 * @return Compiler Fluent interface.
	 */
	public function addStage($name, StageInterface $stage)
	{
		$this->stages[(string) $name] = $stage;
	} // end addStage();
	
	/**
	 * Returns the expression engine with the given name. If there is no expression
	 * engine registered under the given name, an exception is thrown.
	 * 
	 * @throws UnknownResourceException
	 * @param string $name The name of the processing stage.
	 * @return ExpressionInterface
	 */
	public function getStage($name)
	{
		$name = (string)$name;
		if(!isset($this->stages[$name]))
		{
			throw new UnknownResourceException('Cannot find the \''.$name.'\' processing stage.');
		}
		return $this->stages[$name];
	} // end getStage();
	
	/**
	 * Checks if the expression engine with the given name is registered.
	 * 
	 * @param string $name
	 * @return boolean 
	 */
	public function hasStage($name)
	{
		return isset($this->stages[(string)$name]);
	} // end hasStage();
	
	/**
	 * Registers a new expression engine within the compiler under the given name.
	 * The templates may use the name to refer to this expression engine.
	 * 
	 * @param string $name The expression engine name
	 * @param ExpressionInterface $expressionEngine The expression engine.
	 * @return Compiler Fluent interface.
	 */
	public function addExpressionEngine($name, ExpressionInterface $expressionEngine)
	{
		$this->expressionEngines[(string)$name] = $expressionEngine;
		return $this;
	} // end addExpressionEngine();
	
	/**
	 * Returns the expression engine with the given name. If there is no expression
	 * engine registered under the given name, an exception is thrown.
	 * 
	 * @throws UnknownResourceException
	 * @param string $name The name of the expression engine.
	 * @return ExpressionInterface
	 */
	public function getExpressionEngine($name)
	{
		$name = (string)$name;
		if(!isset($this->expressionEngines[$name]))
		{
			throw new UnknownResourceException('Cannot find the \''.$name.'\' expression engine.');
		}
		return $this->expressionEngines[$name];
	} // end getExpressionEngine();
	
	/**
	 * Checks if the expression engine with the given name is registered.
	 * 
	 * @param string $name
	 * @return boolean 
	 */
	public function hasExpressionEngine($name)
	{
		return isset($this->expressionEngines[(string)$name]);
	} // end hasExpressionEngine();
	
	/**
	 * Adds a new namespace URI that will be recognized as the template language
	 * instructions. Note that namespaces do not have to be related to the XML
	 * namespace concept.
	 * 
	 * @param string $uri The new namespace URI.
	 */
	public function addNamespaceURI($uri)
	{
		$uri = (string) $uri;
		$i = sizeof($this->namespaceURI);
		$this->namespaceURI[$i] = $uri;
		$this->reverseURIMapper[$uri] = $i;
	} // end addNamespaceURI();
	
	/**
	 * Checks if the given URI is registered by the template language.
	 * 
	 * @param string $uri The URI to check
	 * @return boolean
	 */
	public function hasNamespaceURI($uri)
	{
		return isset($this->reverseURIMapper[(string)$uri]);
	} // end hasNamespaceURI();
	
	/**
	 * Returns the namespace URI for the given numerical identifier. If the argument
	 * is not a valid ID, an exception is thrown.
	 * 
	 * @throws UnknownResourceException
	 * @param int $id The URI identifier
	 * @return string
	 */
	public function getNamespaceURI($id)
	{
		$id = (int) $id;
		if(!isset($this->namespaceURI[$id]))
		{
			throw new UnknownResourceException('Unknown namespace URI identifier: \''.$id.'\'.');
		}
		return $this->namespaceURI[$id];
	} // end getNamespaceURI();
	
	/**
	 * Returns the numerical URI identifier for the given namespace URI. If
	 * the URI is not registered as a special namespace URI, an exception is
	 * thrown.
	 * 
	 * @throws UnknownResourceException
	 * @param string $uri
	 * @return int
	 */
	public function getURIIdentifier($uri)
	{
		$uri = (string) $uri;
		if(!isset($this->reverseURIMapper[$uri]))
		{
			throw new UnknownResourceException('Unknown namespace URI: \''.$uri.'\'.');
		}
		return $this->reverseURIMapper[$uri];
	} // end getURIIdentifier();
	
	/**
	 * Registers a new instruction in the compiler.
	 * 
	 * @param AbstractInstructionProcessor $instruction 
	 */
	public function addInstruction(AbstractInstructionProcessor $instruction)
	{
		$this->instructions[] = $instruction;
	} // end addInstruction();

	/**
	 * Compiles the unit template and stores the result into the specified
	 * file.
	 *
	 * @param string $sourceName
	 * @param string $compiledName
	 * @param InflectorInterface $inflector
	 */
	public function compile($sourceName, $compiledName, InflectorInterface $inflector)
	{
		try
		{
			// Prepare the compiler elements
			$this->compiledUnit = $compiledUnit = new CompiledUnit();
			
			$this->parser->setCompiler($this);
			$this->linker->setCompiler($this);
			foreach($this->stages as $stage)
			{
				$stage->setCompiler($this);
			}
			foreach($this->instructions as $instruction)
			{
				$instruction->setCompiler($this);
				$instruction->configure();
			}
			
			$finalSnippet = null;

			// The queued templates to be processed.
			$executionQueue = new SplQueue;
			$executionQueue->enqueue($sourceName);

			// These templates requested another templates to
			// be processed first, so they must be reexecuted
			// later.
			$restoreStack = new SplStack;

			// The inheritance loop
			/* Why do we check both the queue and the stack? It is simple. Take a look
			 * at unit test Load/load_extend.txt. The opt:load may be requested by
			 * an extending template. The stack processing is launched, when the queue
			 * becomes empty. However, the last tree on the stack may be opt:extend which
			 * wants to extend another template. We cannot forbid it do so, so we must
			 * fill the queue again and the whole process repeats. So we must wait for
			 * both the stack and the queue to be empty in order to go to the third
			 * compilation stage.
			 */
			while($executionQueue->count() > 0 || $restoreStack->count() > 0)
			{
				while($executionQueue->count() > 0)
				{
					$enqueuedSourceName = $executionQueue->dequeue();
					if($enqueuedSourceName != $sourceName)
					{
						$compiledUnit->addDependency($enqueuedSourceName);
					}
					// The file must be parsed
					$tree = $this->parser->parse(
						$enqueuedSourceName,
						file_get_contents($enqueuedSourceName)
					);
					// The internal representation must be processeed.
					foreach($this->stages as $stage)
					{
						$tree = $stage->process($tree);
					}
					// The template may have requested preprocessing another file first.
				/*	if(null !== ($templates = $tree->get('preprocess')))
					{
						foreach($templates as $preprocessed)
						{
							$executionQueue->enqueue($preprocessed);
						}
						$restoreStack->push($tree);
						continue;
					}
					else
					{
						$this->context->setEscaping(null);
					}
				 */
					list($tree, $finalSnippet) = $this->processPotentialTreeExtending($executionQueue, $tree);
				}
				while(($stackSize = $restoreStack->count()) > 0)
				{
					$tree = $restoreStack->pop();
					// Process it once more
					foreach($this->stages as $stage)
					{
						$tree = $stage->process($stage);
					}
					$compiledUnit->setEscaping(null);

					if($stackSize > 1)
					{
						$tree->dispose();
						unset($tree);
					}
					else
					{
						list($tree, $finalSnippet) = $this->processPotentialTreeExtending($executionQueue, $tree);
					}
				}
			}
			// If the snippet was requested, change it into a root node.
			if(null !== $finalSnippet)
			{

			}

			// Dependencies must be added to the tree.
			if($compiledUnit->getDependencyNumber() > 0)
			{
				$compiledUnit->installDependencies($tree);
			}

			// Linking
			file_put_contents($compiledName, $this->linker->link($tree));
			if($this->linker->hasDynamicBlocks())
			{
				file_put_contents($compiledName.'.dyn', serialize($this->linker->getDynamicBlocks()));
			}

			$tree->dispose();
			$compiledUnit->dispose();
			$this->parser->dispose();
			foreach($this->stages as $stage)
			{
				$stage->dispose();
			}
			foreach($this->instructions as $instruction)
			{
				$instruction->dispose();
			}
			$this->linker->dispose();
		}
		catch(CompilationException $exception)
		{
			$exception->setTemplateName($this->unit->getTemplateName());
			throw $exception;
		}
	} // end compile();


	protected function processPotentialTreeExtending(SplQueue $executionQueue, $tree)
	{
		$finalSnippet = null;
		// The tree may request to be extended.
/*		if(null !== ($extend = $tree->get('extend')))
		{
			$executionQueue->enqueue($extend);
			$tree->dispose();
			unset($tree);
		}
		// Or maybe the finishing tree will be a snippet?
		elseif(null !== ($snippet = $tree->get('snippet')))
		{
			$tree->dispose();
			unset($tree);
			$finalSnippet = $snippet;
		}
*/
		return array($tree, $finalSnippet);
	} // end processPotentialTreeExtending();
} // end Compiler;