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
use Opl\Template\Compiler\Instruction\ProcessingStageInterface;

class IfInstruction extends AbstractInstructionProcessor implements ProcessingStageInterface
{
	/**
	 * @see AbstractInstructionProcessor
	 */
	public function configure()
	{
		$processing = $this->compiler->getStage('process');
		$processing->registerElements($this, 'http://xml.invenzzia.org/declari', array('if'));
	} // end configure();
	
	/**
	 * @see ProcessingStageInterface
	 */
	public function processRuntimeElement(Element $node)
	{
		$attr = $node->getAttribute('test');
		list($bare, $expr, $type) = $this->compiler->getExpressionEngine('parse')->parse($attr->getValue());
		
		$codeBufferCollection = $this->compiler->getCompiledUnit()->getCodeBufferManager()->getProperties($node);
		
		$codeBufferCollection->prepend(CodeBufferCollection::TAG_BEFORE, ' if('.$bare.'){ ');
		$codeBufferCollection->append(CodeBufferCollection::TAG_AFTER, ' } ');
		
		$node->setVisible(true);
		$this->enqueueChildren($node);
	} // end processRuntimeElement();

	/**
	 * @see ProcessingStageInterface
	 */
	public function postProcessRuntimeElement(Element $node)
	{
		
	} // end postProcessRuntimeElement();
	
	/**
	 * @see ProcessingStageInterface
	 */
	public function processRuntimeAttribute(Element $node, Attribute $attribute)
	{
		
	} // end processRuntimeAttribute();

	/**
	 * @see ProcessingStageInterface
	 */
	public function postProcessRuntimeAttribute(Element $node, Attribute $attribute)
	{
		
	} // end postProcessRuntimeAttribute();
} // end IfInstruction;