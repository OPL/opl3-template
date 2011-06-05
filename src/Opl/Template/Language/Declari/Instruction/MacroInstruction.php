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
namespace Opl\Template\Language\Declari\Instruction;
use Opl\Template\Compiler\AST\Attribute;
use Opl\Template\Compiler\AST\Element;
use Opl\Template\Compiler\CodeBufferCollection;
use Opl\Template\Compiler\Instruction\AbstractInstructionProcessor;
use Opl\Template\Compiler\Instruction\ManipulationStageInterface;
use Opl\Template\Compiler\Parser\XmlParser;
use Opl\Template\Exception\InstructionException;
use Opl\Template\Exception\InfiniteRecursionException;
use SplStack;

/**
 * The implementation of the <opt:macro> tag.
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class MacroInstruction extends AbstractInstructionProcessor implements ManipulationStageInterface
{
	/**
	 * The defined macros.
	 * @var array
	 */
	protected $macros = array();
	/**
	 * The defined arguments.
	 * @var array
	 */
	protected $arguments = array();
	/**
	 * The current macros in use.
	 * @var SplStack
	 */
	protected $current;
	
	/**
	 * The property manager for the AST elements.
	 * @var NodePropertyManager
	 */
	protected $propertyManager;
	
	/**
	 * @see AbstractInstructionProcessor
	 */
	public function configure()
	{
		$manipulation = $this->compiler->getStage('manipulate');
		$manipulation->registerElements($this, 'http://xml.invenzzia.org/declari', array('macro', 'use', 'parent'));
		$manipulation->registerAttributes($this, 'http://xml.invenzzia.org/declari', array('use'));
		
		$this->propertyManager = $this->compiler->getCompiledUnit()->getPropertyManager();
		$this->current = new SplStack();
	} // end configure();
	
	/**
	 * @see ManipulationStageInterface
	 */
	public function processManipulationElement(Element $node)
	{
		switch($node->getName())
		{
			case 'macro':
				$params = array(
					'name' => array(0 => XmlParser::REQUIRED, XmlParser::ATTR_ID),
					'__UNKNOWN__' => array(0 => XmlParser::OPTIONAL, XmlParser::ATTR_STRING)
				);
				$arguments = $this->compiler->getParser()->extractAttributes($node, $params);
				$this->createMacro($node, $params['name'], $arguments);
				break;
			case 'use':
				if($node->hasAttribute('captured'))
				{
					
				}
				elseif($node->hasAttribute('procedure'))
				{
					
				}
				else
				{
					$params = array(
						'macro' => array(0 => XmlParser::REQUIRED, XmlParser::ATTR_ID),
						'ignore-default' => array(0 => XmlParser::OPTIONAL, XmlParser::ATTR_BOOLEAN, false),
						'__UNKNOWN__' => array(0 => XmlParser::OPTIONAL, XmlParser::ATTR_STRING)
					);
					$arguments = $this->compiler->getParser()->extractAttributes($node, $params);
					
					if($this->useMacro($node, $params['macro'], $arguments, array('ignoreDefault' => $params['ignore-default'])))
					{
						$properties = $this->propertyManager->getProperties($node);
						$properties->set('postprocess', true);
					}
					$node->setVisible(true);	// TODO: Temporary, remove once we will have an argument support.
					$this->enqueueChildren($node);
				}
				break;
			case 'parent':
				$properties = $this->propertyManager->getProperties($node);
				$name = $properties->get('macro:name');
				$idx = $properties->get('macro:idx');
				
				// If there is a parent, append it here.
				if(isset($this->macros[$name][$idx]))
				{
					// TODO: Set escaping here.
					$node->setEmpty(false);
					$parent = $node->getParent();
					foreach($this->macros[$name][$idx] as $subnode)
					{
						$parent->insertBefore($cloned = clone $subnode, $node);
						$this->enqueueChild($cloned);
					}
					$parent->removeChild($node);
					$node->dispose();
				}
				break;
		}
	} // end processManipulationElement();

	/**
	 * @see ManipulationStageInterface
	 */
	public function postProcessManipulationElement(Element $node)
	{

	} // end postProcessManipulationElement();
	
	/**
	 * @see ManipulationStageInterface
	 */
	public function processManipulationAttribute(Element $node, Attribute $attribute)
	{
		
	} // end processManipulationAttribute();

	/**
	 * @see ManipulationStageInterface
	 */
	public function postProcessManipulationAttribute(Element $node, Attribute $attribute)
	{
		
	} // end postProcessManipulationAttribute();
	
	/**
	 * Registers a new macro within the processor created from the given element.
	 * 
	 * @param Element $node The element we want to change into a macro.
	 * @param string $name The macro name.
	 * @param array $arguments The macro argument definitions.
	 */
	public function createMacro(Element $node, $name, array $arguments)
	{
		// Test the macro arguments.
		if(isset($this->arguments[$name]))
		{
			if($this->arguments[$name] != $arguments)
			{
				throw new InstructionException('The arguments of the \''.$name.'\' macro are incompatible with the previous declaration.');
			}
		}
		else
		{
			$this->arguments[$name] = $arguments;
		}
		
		// Assign the macro
		if(isset($this->macros[$name]))
		{
			$this->macros[$name] = array(0 => $node);
			$current = 0;
		}
		else
		{
			$this->macros[$name][] = $node;
			$current = sizeof($this->macros[$name]);
		}
		
		// TODO: Escaping settings inheritance
		
		// Link "opt:parent" with the parent.
		$parentTags = $node->getElementsByTagNameNs($this->compiler->getURIIdentifier('http://xml.invenzzia.org/declari'), 'parent');
		foreach($parentTags as $parent)
		{
			$properties = $this->propertyManager->getProperties($parent);
			$properties->set('macro:name', $name)
				->set('macro:id', $current);
		}
	} // end createMacro();
	
	/**
	 * Uses a macro in the given element. In order to use a macro, we have to specify
	 * the name and the arguments, if necessary. The available options are:
	 * 
	 *  - <tt>ignoreDefault</tt> - do we ignore the default content of the <use> tag or treat
	 *    it as yet another macro overloading level?
	 *  - <tt>predefinedArguments</tt> - are the arguments predefined and do they need parsing?
	 * 
	 * The method returns true, if the macro was found and successfully used. You need
	 * to call <tt>postUseMacro()</tt> in the postprocessing phase, then.
	 * 
	 * @throws InfiniteRecursionException
	 * @throws InstructionException
	 * @param Element $element The element which wants to use the macro.
	 * @param string $name The macro name.
	 * @param array $arguments The macro arguments.
	 * @param array $options The extra options.
	 * @return boolean 
	 */
	public function useMacro(Element $element, $name, array $arguments, array $options = array())
	{
		$ignoreDefault = (isset($options['ignoreDefault']) ? $options['ignoreDefault'] : false);
		$predefinedArguments = (isset($options['predefinedArguments']) ? $options['predefinedArguments'] : false);
		
		// Detect infinite recursion.
		if($this->isUsed($name))
		{
			$data = array($name);
			foreach($this->current as $info)
			{
				$data[] = $info['name'];
			}
			$err = new InfiniteRecursionException('Infinite macro recursion detected at \''.$name.'\'.');
			throw $err->setStackInfo($data);
		}
		
		$macroBlock = array('name' => $name, 'arguments' => array());
		if(isset($this->macros[$name]))
		{
			// Testing the arguments...
			$startCode = '';
			$i = 0;
			if(!$predefinedArguments)
			{
				// TODO: Write, currently there is no variable context information available.
			}

			// Now we can deal with the macro itself.
			$macro = &$this->macros[$name];

			if($element->hasChildren() && false == $ignoreDefault)
			{
				$newNode = new Element($this->compiler->getURIIdentifier('http://xml.invenzzia.org/declari'), 'temporary');
				// TODO: Remember the escaping settings.
				$element->moveChildren($newNode);
				$size = sizeof($macro);
				$macro[$size] = $newNode;
				$macroBlock['useSize'] = $size;
			}
			foreach($element->removeChildren() as $child)
			{
				$child->dispose();
			}
			foreach($macro[0] as $subnode)
			{
				$element->appendChild(clone $subnode);
			}
			$this->current->push($macroBlock);
			$properties = $this->propertyManager->getProperties($element);
			$properties->set('usedMacro', $name);
			return true;
		}
		return false;		
	} // end useMacro();
	
	/**
	 * Terminates the macro usage. The programmer should call this method in
	 * the post-processing phase, if <tt>useMacro()</tt> returned true.
	 * 
	 * @param Element $element The element where the macro is being used.
	 */
	public function postUseMacro(Element $element)
	{
		if($this->current->count() == 0)
		{
			return;
		}
		$info = $this->current->pop();
		
		// Freeing the fake node, if necessary.
		if(isset($info['useSize']))
		{
			$this->macros[$info['name']][$info['useSize']]->dispose();
			unset($this->macros[$info['name']][$info['useSize']]);
		}
		
		// Clean the argument information
		
		// Restore the original escaping state.
	} // end postUseMacro();
	
	/**
	 * Returns true, if the macro with the given name exists.
	 * 
	 * @param string $name The macro name.
	 * @return boolean
	 */
	public function hasMacro($name)
	{
		return isset($this->macros[(string) $name]);
	} // end hasMacro();
	
	/**
	 * Returns true, if the macro is in use right now.
	 * 
	 * @param string $name The macro name.
	 * @return boolean
	 */
	public function isUsed($name)
	{
		foreach($this->current as $info)
		{
			if($info['name'] == $name)
			{
				return true;
			}
		}
		return false;
	} // end isUsed();
} // end MacroInstruction;