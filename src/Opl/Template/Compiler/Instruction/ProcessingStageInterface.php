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
use Opl\Template\Compiler\AST\Attribute;
use Opl\Template\Compiler\AST\Element;

/**
 * This interface must be implemented by all the instruction processors
 * that wish to be called by the processing stage.
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
interface ProcessingStageInterface
{
	public function processRuntimeElement(Element $node);
	public function postProcessRuntimeElement(Element $node);
	
	public function processRuntimeAttribute(Element $node, Attribute $attribute);
	public function postProcessRuntimeAttribute(Element $node, Attribute $attribute);
} // end ProcessingStageInterface;