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

/**
 * The class represents the entire loaded document.
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class Document extends Scannable
{
	/**
	 * Specifies the document type.
	 * @var string
	 */
	private $documentType;
	
	/**
	 * Various extra meta-nodes are stored here.
	 * @var array
	 */
	private $extraNodes = array();
	
	/**
	 * Initializes the document. The document type provides an extra information
	 * for some parts of the compiler or the language implementation.
	 * 
	 * @param string $documentType The document type.
	 */
	public function __construct($documentType)
	{
		$this->documentType = (string)$documentType;
	} // end __construct();
	
	/**
	 * Returns the document type.
	 * 
	 * @return string 
	 */
	public function getDocumentType()
	{
		return $this->documentType;
	} // end getDocumentType();
	
	/**
	 * Documents are always the top-level nodes, so an attempt to call this method
	 * results in throwing an exception.
	 * 
	 * @throws BadMethodCallException
	 * @param Node $parent The parent node.
	 */
	public function setParent(Node $parent = null)
	{
		throw new BadMethodCallException('Cannot set a parent node for the Document node.');
	} // end setParent();
	
	/**
	 * Creates an extra node within the document. Extra nodes may be any valid PHP objects
	 * and provide some extra information for the further processing.
	 * 
	 * @param string $name The extra node name.
	 * @param object $node The extra node object.
	 * @return Document Fluent interface.
	 */
	public function setExtraNode($name, $node)
	{
		if(!is_object($node))
		{
			throw new DomainException('The second argument of addExtraNode() must be an object.');
		}
		$this->extraNodes[(string)$name] = $node;
		
		return $this;
	} // end setExtraNode();
	
	/**
	 * Returns the specified extra node. If the node does not exist, an exception
	 * is thrown.
	 * 
	 * @throws DomainException
	 * @param string $name The extra node name.
	 * @return object
	 */
	public function getExtraNode($name)
	{
		$name = (string)$name;
		if(!isset($this->extraNodes[$name]))
		{
			throw new DomainException('The document extra node \''.$name.'\' is not defined.');
		}
		return $this->extraNodes[$name];
	} // end getExtraNode();
	
	/**
	 * Checks if the given extra node is actually defined.
	 * 
	 * @param string $name The extra node name.
	 * @return boolean
	 */
	public function hasExtraNode($name)
	{
		return isset($this->extraNodes[(string)$name]);
	} // end hasExtraNode();
} // end Document;