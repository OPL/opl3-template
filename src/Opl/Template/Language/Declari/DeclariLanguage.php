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
namespace Opl\Template\Language\Declari;
use Opl\Template\Compiler\Compiler;
use Opl\Template\Compiler\Linker\XmlLinker;
use Opl\Template\Compiler\Expression\StringExpression;
use Opl\Template\Compiler\LanguageInterface;
use Opl\Template\Compiler\Stage\ManipulationStage;
use Opl\Template\Compiler\Stage\ProcessingStage;
use Opl\Template\Compiler\Parser\XmlParser;
use Opl\Template\Exception\LanguageException;
use Opl\Template\Language\Declari\Expression\DeclariExpression;
use Opl\Template\Language\Declari\Instruction\IfInstruction;
use Opl\Template\Language\Declari\Instruction\MacroInstruction;
use Opl\Template\Language\Declari\Instruction\TemplateInstruction;

/**
 * An initializer of the Declari template language.
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class DeclariLanguage implements LanguageInterface
{
	/**
	 * @see LanguageInterface
	 */
	public function initializeLanguage(Compiler $compiler)
	{
		if($compiler->getParser() !== null)
		{
			throw new LanguageException('Cannot initialize Declari: another template language is selected.');
		}
		
		$compiler->setParser($parser = new XmlParser());
		$compiler->setLinker(new XmlLinker());
		$compiler->addStage('manipulate', new ManipulationStage());
		$compiler->addStage('process', new ProcessingStage());
		
		$parser->setDefaultExpressionType('parse');
		
		$compiler->addNamespaceURI('http://xml.invenzzia.org/declari');
		$compiler->addExpressionEngine('parse', new DeclariExpression());
		$compiler->addExpressionEngine('str', new StringExpression());
		$compiler->addInstruction(new TemplateInstruction());
		$compiler->addInstruction(new MacroInstruction());
		$compiler->addInstruction(new IfInstruction());
	} // end initializeLanguage();
} // end DeclariLanguage;