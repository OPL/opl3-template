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
use Opl\Template\Compiler\AST\Document;
use Opl\Template\Compiler\CodeBufferCollection;
use Opl\Template\Compiler\Instruction\AbstractInstructionProcessor;
use Opl\Template\Compiler\Instruction\ManipulationStageInterface;
use Opl\Template\Compiler\Instruction\InheritanceHookInterface;
use Opl\Template\Compiler\Parser\XmlParser;
use Opl\Template\Exception\InstructionException;
use Opl\Template\Exception\AttributeExtractionException;

/**
 * The implementation of the <opt:template>, <opt:extend> and <opt:load> tags.
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class TemplateInstruction extends AbstractInstructionProcessor implements ManipulationStageInterface, InheritanceHookInterface
{
	/**
	 * The list of the loaded documents.
	 * @var array 
	 */
	protected $documents = array();
	
	/**
	 * @var XmlParser 
	 */
	protected $parser;
	
	/**
	 * @see AbstractInstructionProcessor
	 */
	public function configure()
	{
		$this->compiler->setInheritanceHook($this);		
		$manipulation = $this->compiler->getStage('manipulate');
		$manipulation->registerElements($this, 'http://xml.invenzzia.org/declari', array('load'));

		$this->parser = $this->compiler->getParser();
	} // end configure();
	
	/**
	 * @see InheritanceHookInterface
	 */
	public function checkInheritance(Document $document)
	{
		$declariId = $this->compiler->getURIIdentifier('http://xml.invenzzia.org/declari');
		$item = $document->getRootElement();
		if($item->getURIIdentifier() == $declariId && ($item->getName() == 'template' || $item->getName() == 'extend'))
		{
			// Find all the templates to include on compile-time
			$includes = array();
			$extend = null;
			$loads = $item->getElementsByTagNameNS($declariId, 'load');
			foreach($loads as $load)
			{
				$params = array(
					'template' => array(XmlParser::REQUIRED, XmlParser::ATTR_STRING)
				);
				$this->parser->extractAttributes($load, $params);
				$includes[] = $params['template'];
			}
			$params = array(
				'dynamic' => array(XmlParser::OPTIONAL, XmlParser::ATTR_BOOLEAN, false),
				'escaping' => array(XmlParser::OPTIONAL, XmlParser::ATTR_BOOLEAN, true),
				'__UNKNOWN__' => array(XmlParser::OPTIONAL, XmlParser::ATTR_STRING)
			);
			$extends = $this->parser->extractAttributes($item, $params);
			if($params['dynamic'])
			{
				// TODO: Write
			}
			elseif($item->getName() == 'extend')
			{
				if(!isset($extends['file']))
				{
					throw new AttributeExtractionException('The required attribute \'file\' in \''.$item->getFullyQualifiedName().'\' is not defined.');
				}
				$extend = $extends['file'];
				// TODO: Add inheritance branches!
			}
			$item->setVisible(true);
			return array($extend, $includes);
		}
		return array(null, null);
	} // end checkInheritance();
	
	/**
	 * @see InheritanceHookInterface
	 */
	public function handleInheritance($sourceName, Document $document)
	{
		$this->documents[$sourceName] = $document;
	} // end handleInheritance();
	
	/**
	 * @see ManipulationStageInterface
	 */
	public function processManipulationElement(Element $node)
	{
		$params = array(
			'template' => array(XmlParser::REQUIRED, XmlParser::ATTR_STRING)
		);
		$this->parser->extractAttributes($node, $params);
		
		// Unless a bug in the code, we can be sure that this key exists.
		$document = $this->documents[$params['template']];
		$parent = $node->getParent();
		$children = $document->getRootElement()->getChildren();

		// Insert the children into the current tree.
		foreach($children as $child)
		{
			$clonedChild = clone $child;

			$parent->insertBefore($clonedChild, $node);
			$this->enqueueChild($clonedChild);
		}
		$parent->removeChild($node);
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
	 * @see AbstractInstructionProcessor
	 */
	public function dispose()
	{
		parent::dispose();
		foreach($this->documents as $document)
		{
			$document->dispose();
		}
		$this->parser = null;
	} // end dispose();
} // end TemplateInstruction;
