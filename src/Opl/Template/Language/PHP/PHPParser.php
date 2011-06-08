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
use Opl\Template\Compiler\Compiler;
use Opl\Template\Compiler\Parser\ParserInterface;

/**
 * The parser implementation for the PHP template language. It constructs
 * the PHP-specific Abstract Syntax Tree.
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class PHPParser implements ParserInterface
{
	/**
	 * The compiler instance
	 * @var Opl\Template\Compiler\Compiler 
	 */
	private $compiler;
	
	/**
	 * @see ParserInterface
	 */
	public function setCompiler(Compiler $compiler)
	{
		$this->compiler = $compiler;
	} // end setCompiler();
	
	/**
	 * @see ParserInterface
	 */
	public function parse($filename)
	{
		
	} // end parse();
	
	/**
	 * @see ParserInterface
	 */
	public function dispose()
	{
		$this->compiler = null;
	} // end dispose();
} // end PHPParser;