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
namespace Opl\Template\Compiler\Parser;
use Opl\Template\Compiler\AST\Attribute;
use Opl\Template\Compiler\AST\Cdata;
use Opl\Template\Compiler\AST\Comment;
use Opl\Template\Compiler\AST\Document;
use Opl\Template\Compiler\AST\Element;
use Opl\Template\Compiler\AST\Expression;
use Opl\Template\Compiler\AST\Node;
use Opl\Template\Compiler\AST\Text;
use Opl\Template\Compiler\AST\Scannable;
use Opl\Template\Compiler\Compiler;
use Opl\Template\Exception\ParserException;
use RuntimeException;
use XMLReader;

/**
 * This class provides an universal XML parser for the templates which produces
 * a classic Abstract Syntax Tree, using the classes from the <tt>\Opl\Template\Compiler\AST</tt>
 * namespace. The class 
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class XmlParser implements ParserInterface
{
	const EXPRESSION_TAG = '/(\{([^\}]*)\})/msi';
	
	/**
	 * The compiler instance
	 * @var Opl\Template\Compiler\Compiler 
	 */
	private $compiler;
	
	private $defaultExpressionType;
	
	public function setDefaultExpressionType($type)
	{
		$this->defaultExpressionType = (string) $type;
	} // end setDefaultExpressionType();
	
	public function getDefaultExpressionType()
	{
		return $this->defaultExpressionType;
	} // end getDefaultExpressionType();
	
	/**
	 * @see ParserInterface
	 */
	public function setCompiler(Compiler $compiler)
	{
		if(!extension_loaded('XMLReader'))
		{
			throw new RuntimeException('The XML parser used by Open Power Template requires an XMLReader extension to be loaded.');
		}
		$this->compiler = $compiler;
	} // end setCompiler();
	
	/**
	 * @see ParserInterface
	 */
	public function parse($filename)
	{
		$debug = array(
			XMLReader::NONE => 'NONE',
			XMLReader::ELEMENT => 'ELEMENT',
			XMLReader::ATTRIBUTE => 'ATTRIBUTE',
			XMLReader::TEXT => 'TEXT',
			XMLReader::CDATA => 'CDATA',
			XMLReader::ENTITY_REF => 'ENTITY_REF',
			XMLReader::ENTITY => 'ENTITY',
			XMLReader::PI => 'PI',
			XMLReader::COMMENT => 'COMMENT',
			XMLReader::DOC => 'DOC',
			XMLReader::DOC_TYPE => 'DOC_TYPE',
			XMLReader::DOC_FRAGMENT => 'DOC_FRAGMENT',
			XMLReader::NOTATION => 'NOTATION',
			XMLReader::WHITESPACE => 'WHITESPACE',
			XMLReader::SIGNIFICANT_WHITESPACE => 'SIGNIFICANT_WHITESPACE',
			XMLReader::END_ELEMENT => 'END_ELEMENT',
			XMLReader::END_ENTITY => 'END_ENTITY',
			XMLReader::XML_DECLARATION => 'XML_DECLARATION'
		);
		
		libxml_use_internal_errors(true);
		
		$reader = new XMLReader;
		$reader->open($filename);
		
		$root = $current = new Document('xml');
		$firstElementMatched = false;
		$depth = 0;
		
		// Yet Another Extension Written By Morons Who Don't Know What Exceptions Are...
		while(@$reader->read())
		{
			if($reader->depth < $depth)
			{
				$current = $current->getParent();
			}
			elseif($reader->depth > $depth)
			{
				$current = $optNode;
			}
			switch($reader->nodeType)
			{
				case XMLReader::ELEMENT:
					$name = explode(':', $reader->name);
					if(sizeof($name) == 2)
					{
						$optNode = new Element($name[0], $name[1]);
					}
					else
					{
						$optNode = new Element(null, $name[0]);
					}
					if($this->compiler->hasNamespaceURI($reader->namespaceURI))
					{
						$optNode->setURIIdentifier($this->compiler->getURIIdentifier($reader->namespaceURI));
					}
					// Parse element attributes, if you manage to get there
					if($reader->moveToFirstAttribute())
					{
						do
						{
							// "xmlns" special namespace must be handler somehow differently.
							if($reader->prefix == 'xmlns')
							{
								// TODO: Implement
							}
							else
							{
								$name = explode(':', $reader->name);
								if(sizeof($name) == 2)
								{
									$optAttribute = new Attribute($name[0], $name[1]);
								}
								else
								{
									$optAttribute = new Attribute(null, $name[0]);
								}
								
								if($this->compiler->hasNamespaceURI($reader->namespaceURI))
								{
									$optAttribute->setURIIdentifier($this->compiler->getURIIdentifier($reader->namespaceURI));
								}
								
								$optAttribute->setValue($this->compileValue($reader->value));
								$optNode->addAttribute($optAttribute);
							}
						}
						while($reader->moveToNextAttribute());
						$reader->moveToElement();
					}
					// Set the "emptiness" status
					if($reader->isEmptyElement)
					{
						$optNode->setEmpty(true);
					}
					$current->appendChild($optNode);
					break;
				case XMLReader::TEXT:
				case XMLReader::WHITESPACE:
				case XMLReader::SIGNIFICANT_WHITESPACE:
					$this->parseCdata($current, $reader->value);
					break;
				case XMLReader::COMMENT:
					break;
				
				case XMLReader::CDATA:
					$cdata = new Cdata($reader->value);
					
					// TODO: We must also inform somehow that this is a true CDATA.					
					$this->appendTextNode($current, $cdata);
					break;
			}
			$depth = $reader->depth;
		}
		$errors = libxml_get_errors();
		if(sizeof($errors) > 0)
		{
			libxml_clear_errors();
			$msg = current($errors);

			throw new ParserException($msg->message, 'XML', $msg->line);
		}
		
		return $root;
	} // end parse();
	
	/**
	 * @see ParserInterface
	 */
	public function dispose()
	{
		$this->compiler = null;
	} // end dispose();
	
	/**
	 * Extracts the expression type from the specified expression string. The
	 * returned value is an array containing the type and the actual expression.
	 * Note that the method does not check if the expression type is registered
	 * within the compiler.
	 * 
	 * If the expression string does not define the type explicitely, the method
	 * takes the suggested type, or (if it is not set) the default expression
	 * type set in the parser.
	 * 
	 * @param string $expression The expression to parse
	 * @param string $suggested The suggested expression type.
	 * @return array
	 */
	public function detectExpressionType($expression, $suggested = null)
	{
		if(preg_match('/^([a-zA-Z0-9\_]{2,})\:([^\:].*)$/', $expression, $found))
		{
			return array($found[1], $found[2]);
		}
		elseif(null !== $suggested)
		{
			return array($suggested, $expression);
		}
		return array($this->defaultExpressionType, $expression);
	} // end detectExpressionType();
	
	/**
	 * Parses the Character Data element, extracting expressions and producing valid
	 * AST nodes.
	 * 
	 * @internal
	 * @todo The regular expression responsible for parsing the expression should be configurable.
	 * @param \Opl\Template\Compiler\AST\Node $current The current node
	 * @param string $text The text to parse
	 * @return \Opl\Template\Compiler\AST\Node The new current node
	 */
	protected function parseCdata(Node $current, $text)
	{
		preg_match_all(self::EXPRESSION_TAG, $text, $result, PREG_SET_ORDER);
		
		$resultSize = sizeof($result);
		$offset = 0;
		for($i = 0; $i < $resultSize; $i++)
		{
			$id = strpos($text, $result[$i][0], $offset);
			if($id > $offset)
			{
				$current = $this->appendTextNode($current, substr($text, $offset, $id - $offset));
			}
			$offset = $id + strlen($result[$i][0]);
			
			$expr = $this->detectExpressionType($result[$i][2]);
			$current = $this->appendTextNode($current, new Expression($expr[1], $expr[0]));
		}
		
		$i--;
		// Now the remaining part of the text.
		if(strlen($text) > $offset)
		{
			$current = $this->appendTextNode($current, substr($text, $offset, strlen($text) - $offset));
		}
		return $current;
	} // end parseCdata();
	
	/**
	 * An utility method that simplifies inserting the text to the XML
	 * tree. Depending on the last child type, it can create a new text
	 * node or add the text to the existing one.
	 *
	 * @internal
	 * @param Node $current The currently built XML node.
	 * @param string|Node $text The text or the expression node.
	 * @return Node The current XML node.
	 */
	protected function appendTextNode(Node $current, $text)
	{
		$last = $current->getLastChild();		
		if(!is_object($last) || ! $last instanceof Text)
		{
			if(!is_object($text))
			{
				$node = new Text();
				$node->appendData($text);
			}
			else
			{
				$node = new Text();
				$node->appendChild($text);
			}
			$current->appendChild($node);
		}
		else
		{
			if(!is_object($text))
			{
				$last->appendData($text);
			}
			else
			{
				$last->appendChild($text);
			}
		}
		return $current;
	} // end appendTextNode();
	
	/**
	 * Compiles the attribute values. If the attribute contains some expression
	 * declaration, it is evaluated into an expression node.
	 * 
	 * @param string $value 
	 * @return mixed
	 */
	protected function compileValue($value)
	{
		if(preg_match('/^([a-zA-Z0-9\_]{2,})\:([^\:].*)$/', $value, $found))
		{
			if($found[1] == 'null')
			{
				return $found[2];
			}
			else
			{
				return new Expression($found[2], $found[1]);
			}
			return array($found[1], $found[2]);
		}
		return $value;
	} // end compileValue();
} // end XmlParser;