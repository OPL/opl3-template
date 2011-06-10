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
		$content = file_get_contents($filename);
		$length = sizeof($content);
		
		$offset = 0;
		$current = null;
		$mode = 0;
		$prependedCode = '';
		
		$root = new Document('php');
		
		while($offset < $length)
		{
			if($mode == 0)
			{
				$nextPos = strpos($content, '<?php', $offset);
				if(false == $nextPos)
				{
					$nextPos = strpos($content, '<?=', $offset);
					if(false !== $nextPos)
					{
						$prependedCode = ' echo ';
					}
					else
					{
						$nextPos = $length;
					}					
				}
				$node = new StaticText(substr($content, $offset, $nextPos - $offset));
				$mode = 1;
				$offset = $nextPos;

			}
			else
			{
				$nextPos = strpos($content, '?'.'>', $offset);
				if(false == $nextPos)
				{
					$nextPos = $length;
				}
				$node = new Code($prependedCode.substr($content, $offset, $nextPos - $offset));
				$mode = 1;
				$offset = $nextPos;
				$prependedCode = '';
			}
			$root->appendChild($node);
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