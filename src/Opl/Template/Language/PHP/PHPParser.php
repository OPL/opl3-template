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
use Opl\Template\Compiler\Compiler;
use Opl\Template\Compiler\Parser\ParserInterface;
use Opl\Template\Language\PHP\AST\Code;

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
		$content = file_get_contents($filename);
		$length = strlen($content);
		
		$offset = 0;
		$current = null;
		$mode = 0;
		$prependedCode = '';
		
		$document = new Document('php');
		
		while($offset < $length)
		{
			if($mode == 0)
			{
				$testPos1 = strpos($content, '<?php', $offset);
				$testPos2 = strpos($content, '<?=', $offset);
				
				if(false !== $testPos1 && $testPos1 < $testPos2)
				{
					$nextPos = $testPos1;
					$mv = 5;
				}
				else
				{
					$nextPos = $testPos2;
					$mv = 3;
					$prependedCode = ' echo ';
				}
			
				if(false === $nextPos)
				{
					$nextPos = $length;
					$mv = 0;
					$prependedCode = '';
				}
				$node = new Code(Code::PLAIN_TYPE, $txt = substr($content, $offset, $nextPos - $offset));
				$mode = 1;
				$offset = $nextPos + $mv;
			}
			else
			{
				$nextPos = strpos($content, '?'.'>', $offset);
				if(false === $nextPos)
				{
					$nextPos = $length;
					$mv = 0;
				}
				else
				{
					$mv = 2;
				}
				$node = new Code(Code::PHP_TYPE, $txt = $prependedCode.substr($content, $offset, $nextPos - $offset));
				$mode = 0;
				$offset = $nextPos + $mv;
				$prependedCode = '';
			}
			$document->appendChild($node);
		}

		return $document;		
	} // end parse();
	
	/**
	 * @see ParserInterface
	 */
	public function dispose()
	{
		$this->compiler = null;
	} // end dispose();
} // end PHPParser;