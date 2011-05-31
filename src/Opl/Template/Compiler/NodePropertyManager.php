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
use Opl\Template\Compiler\AST\Node;
use Countable;
use SplObjectStorage;

/**
 * The compiler usually wants to store some extra information in the AST
 * nodes useful for the linker and related to the output code generation.
 * We do not want to put this stuff directly in the nodes for several reasons:
 * 
 *  - we do not know all the possible use cases, especially for the third party
 *    languages
 *  - problems with lazy initialization of the data structures
 *  - problems with possible circular references
 * 
 * This property manages stores the node properties outside them and provides
 * a convenient wrapper to obtain them.
 */
class NodePropertyManager implements Countable
{
	/**
	 * The node database.
	 * @var SplObjectStorage
	 */
	protected $nodes;
	
	/**
	 * The property collection class name.
	 * @var string
	 */
	protected $className;
	
	/**
	 * Creates the node property manager.
	 * 
	 * @param string $className The class name representing the property collection for the nodes.
	 */
	public function __construct($className)
	{
		$this->className = $className;
		$this->nodes = new SplObjectStorage();
	} // end __construct();
	
	/**
	 * Returns the property collection for the given node. If the node does
	 * not have its own property collection yet, it is automatically created.
	 * 
	 * @param Node $node The node
	 * @return object The property collection for the given node.
	 */
	public function getProperties(Node $node)
	{
		if(!$this->nodes->contains($node))
		{
			$className = $this->className;
			$this->nodes->attach($node, $nodeProperties = new $className());
			return $nodeProperties;
		}
		else
		{
			return $this->nodes[$node];
		}
	} // end getProperties();
	
	/**
	 * Returns the number of nodes supported by this manager.
	 * 
	 * @return int 
	 */
	public function count()
	{
		return $this->nodes->count();
	} // end count();
	
	/**
	 * Frees the memory.
	 */
	public function dispose()
	{
		foreach($this->nodes as $nodeProperties)
		{
			$nodeProperties->dispose();
		}
		$this->nodes = new SplObjectStorage(); 
	} // end dispose();
} // end NodePropertyManager;