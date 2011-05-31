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

// TEMPORARY BELOW!
use Opl\Template\Compiler\Linker\XmlLinker;
use Opl\Template\Compiler\Expression\StringExpression;
use Opl\Template\Compiler\Stage\ProcessingStage;
use Opl\Template\Compiler\Parser\XmlParser;
use Opl\Template\Language\Declari\Expression\DeclariExpression;

class CompilerFactory implements CompilerFactoryInterface
{
	public function getCompiler()
	{
		$compiler = new Compiler();
		$compiler->setParser($parser = new XmlParser());
		$compiler->setLinker(new XmlLinker());
		$compiler->addStage('process', new ProcessingStage());
		
		$parser->setDefaultExpressionType('parse');

		$compiler->addExpressionEngine('parse', new DeclariExpression());
		$compiler->addExpressionEngine('str', new StringExpression());
		
		return $compiler;
	} // end getCompiler();
} // end CompilerFactory;