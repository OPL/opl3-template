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

/**
 * The class provides the functionality of code buffers, where the compiler
 * may store pieces of PHP code that will be attached to the output template
 * around the tag.
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class CodeBufferCollection
{
	const TAG_BEFORE = 0;
	const TAG_AFTER = 1;
	const TAG_OPENING_BEFORE = 2;
	const TAG_OPENING_AFTER = 3;
	const TAG_CLOSING_BEFORE = 4;
	const TAG_CLOSING_AFTER = 5;
	const TAG_CONTENT_BEFORE = 6;
	const TAG_CONTENT_AFTER = 7;
	const TAG_CONTENT = 17;

	const TAG_SINGLE_BEFORE = 18;
	const TAG_SINGLE_AFTER = 19;

	const TAG_NAME = 8;
	const TAG_ATTRIBUTES_BEFORE = 9;
	const TAG_ATTRIBUTES_AFTER = 10;
	const TAG_BEGINNING_ATTRIBUTES = 11;
	const TAG_ENDING_ATTRIBUTES = 12;
	const ATTRIBUTE_BEGIN = 13;
	const ATTRIBUTE_END = 14;
	const ATTRIBUTE_NAME = 15;
	const ATTRIBUTE_VALUE = 16;

	/**
	 * Code buffer content is here.
	 * @var array
	 */
	protected $buffers = array();
	
	/**
	 * Prepends the code to the specified buffer.
	 * 
	 * @param int $buffer The buffer ID
	 * @param string $code The code to prepend
	 * @return CodeBufferCollection Fluent interface.
	 */
	public function prepend($buffer, $code)
	{
		if(!isset($this->buffers[$buffer]))
		{
			$this->buffers[$buffer] = '';
		}
		$this->buffers[$buffer] = ((string)$code).' '.$this->buffers[$buffer];
		return $this;
	} // end prepend();
	
	/**
	 * Appends the code to the specified buffer.
	 * 
	 * @param int $buffer The buffer ID
	 * @param string $code The code to append
	 * @return CodeBufferCollection Fluent interface.
	 */
	public function append($buffer, $code)
	{
		if(!isset($this->buffers[$buffer]))
		{
			$this->buffers[$buffer] = '';
		}
		$this->buffers[$buffer] .= ' '.(string)$code;		
		return $this;
	} // end append();

	/**
	 * Copies the code from another code buffer collection to the specified buffer.
	 * If the buffer already contains some code, the copied content is appended to
	 * it.
	 * 
	 * @param CodeBufferCollection $srcCollection The source code buffer collection.
	 * @param int $srcBuffer The source buffer in the source buffer collection.
	 * @param int $destBuffer The destination buffer in the current code buffer collection.
	 * @return CodeBufferCollection Fluent interface.
	 */
	public function copy(CodeBufferCollection $srcCollection, $srcBuffer, $destBuffer)
	{
		if(isset($this->buffers[$buffer]))
		{
			$this->buffers[$destBuffer] .= $srcCollection->getBuffer($srcBuffer);
		}
		else
		{
			$this->buffers[$destBuffer] = $srcCollection->getBuffer($srcBuffer);
		}
		return $this;
	} // end copy();
	
	/**
	 * Returns the content of the given code buffer.
	 * 
	 * @param int $buffer The buffer ID
	 * @return string 
	 */
	public function getBuffer($buffer)
	{
		if(!isset($this->buffers[$buffer]))
		{
			return '';
		}
		return $this->buffers[$buffer];
	} // end getBuffer();
	
	/**
	 * Returns true, if the given buffer has any content.
	 * 
	 * @param int $buffer The buffer ID.
	 * @return boolean 
	 */
	public function hasContent($buffer)
	{
		return !empty($this->buffers[$buffer]);
	} // end hasContent();
	
	/**
	 * Clears the code buffer collection.
	 * 
	 * @return CodeBufferCollection Fluent interface.
	 */
	public function clear()
	{
		$this->buffers = array();
		return $this;
	} // end clear();
	
	/**
	 * Frees the memory.
	 */
	public function dispose()
	{
		$this->buffers = null;
	} // end dispose();
	
	/**
	 * Links the specified list of code buffer into a single output PHP code
	 * text. If the second argument is set to true, the output code is treated
	 * as a static text and is not enclosed within PHP opening and closing tags.
	 * 
	 * The array of buffers to link might also contain strings with static text which
	 * will be mingled with the code buffer contents.
	 * 
	 * @param array $buffers The buffers to link.
	 * @param boolean $noPhp Do not treat the buffer content as a PHP code.
	 * @return string
	 */
	public function link(array $buffers, $noPhp = false)
	{
		$out = '';
		$used = false;
		foreach($buffers as $bufferName)
		{
			if(is_string($bufferName))
			{
				if($used)
				{
					$out .= ($noPhp ? $bufferName : ' echo \''.$bufferName.'\'');
				}
				$used = false;
				continue;
			}
			if(isset($this->buffers[$bufferName]))
			{
				$out .= $this->buffers[$bufferName];
				$used = true;
			}
			else
			{
				$used = false;
			}
		}
		if(strlen($out) > 0)
		{
			return ($noPhp ? trim($out) : '<'.'?php '.$out.' ?'.'>');
		}
		return '';
	} // end link();
} // end CodeBufferCollection;
