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
namespace Opl\Template\Compiler\AST;
use Opl\Template\Exception\ASTException;
use OutOfRangeException;

/**
 * A node type representing static character data normally
 * printed by the template to the output.
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class Cdata extends Node
{
	/**
	 * @var string
	 */
	private $text;
	
	/**
	 * Initializes the new Cdata node with the given text.
	 * 
	 * @param string $text The initial content.
	 */
	public function __construct($text)
	{
		$this->text = (string)$text;
	} // end __construct();
	
	/**
	 * Appends the string to the existing node content.
	 * 
	 * @param string $text The text to append.
	 * @return Cdata Fluent interface.
	 */
	public function appendData($text)
	{
		$this->text .= $text;
		return $this;
	} // end appendData();
	
	/**
	 * Inserts the string in the specified offset.
	 * 
	 * @throws OutOfRangeException If the offset is out of the current string bounds.
	 * @param int $offset The offset.
	 * @param string $cdata The new string.
	 * @return Cdata Fluent interface.
	 */
	public function insertData($offset, $cdata)
	{
		$length = strlen($this->text);
		if($offset < 0 || $offset >= $length)
		{
			throw new OutOfRangeException('Invalid Cdata offset: '.$offset);
		}
		$this->text = substr($this->text, 0, $offset).$cdata.substr($this->text, $offset, $length-$offset);
		
		return $this;
	} // end insertData();
	
	/**
	 * Deletes the specified part of the content.
	 * 
	 * @throws OutOfRangeException If the offset is out of the current string bounds.
	 * @param int $offset The position of the first character to delete.
	 * @param int $count The number of characters to delete.
	 * @return Cdata Fluent interface.
	 */
	public function deleteData($offset, $count)
	{
		$length = strlen($this->text);
		if($offset < 0 || $offset >= $length || ($offset + $count) >= $length)
		{
			throw new OutOfRangeException('Invalid Cdata offset: '.$offset);
		}
		$this->text = substr($this->text, 0, $offset).substr($this->text, $offset+$count, $length-$offset-$count);
		
		return $this;
	} // end deleteData();
	
	/**
	 * Replaces the specified amount of the original text with the part of the new string.
	 * 
	 * @throws OutOfRangeException If the offset is out of the current string bounds.
	 * @param int $offset The position of the first character to replace.
	 * @param int $count The number of characters to replace.
	 * @param string $text The replacing string.
	 * @return Cdata Fluent interface.
	 */
	public function replaceData($offset, $count, $text)
	{
		$length = strlen($this->text);
		if($offset < 0 || $offset >= $length || ($offset + $count) >= $length)
		{
			throw new OutOfRangeException('Invalid Cdata offset: '.$offset);
		}
		$this->text = substr($this->text, 0, $offset).substr($text, 0, $count).substr($this->text, $offset+$count, $length-$offset-$count);
		
		return $this;
	} // end replaceData();
	
	/**
	 * Returns the specified part of the content.
	 * 
	 * @param int $offset The position of the first character to return.
	 * @param int $count The number of characters to return.
	 * @return string
	 */
	public function substringData($offset, $count)
	{
		return substr($this->text, $offset, $count);
	} // end substringData();
	
	/**
	 * Returns the content length.
	 * 
	 * @return int 
	 */
	public function length()
	{
		return strlen($this->text);
	} // end length();
	
	/**
	 * Returns the content.
	 * 
	 * @return string 
	 */
	public function __toString()
	{
		return $this->text;
	} // end __toString();
} // end Cdata;