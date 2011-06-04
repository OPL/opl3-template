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
use SplQueue;

/**
 * Represents a typical element of the HTML language, or the template language.
 * Semantically, it is very similar to XML elements. It has a name, an optional
 * namespace, and can possess some attributes.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class Element extends Scannable implements NamedElementInterface, NamespacedElementInterface
{
	private $name;
	private $namespace;
	private $uriID;
	private $attributes = array();
	private $empty = false;
	
	/**
	 * Creates the Element with the given name. If the element is not assigned
	 * to any namespace, the namespace should be set to null.
	 *
	 * @throws ASTException The element name cannot be null.
	 * @param string|null $namespace The element namespace name or null.
	 * @param string $name The element name.
	 */
	public function __construct($namespace, $name)
	{
		$this->namespace = $namespace;
		$this->setName($name);
	} // end __construct();
	
	/**
	 * @see NamedElementInterface
	 */
	public function setName($name)
	{
		if(empty($name))
		{
			throw new ASTException('The OPT AST element name cannot be empty.');
		}
		$this->name = $name;
	} // end setName();
	
	/**
	 * @see NamedElementInterface
	 */
	public function getName()
	{
		return $this->name;
	} // end getName();
	
	/**
	 * @see NamespacedElementInterface
	 */
	public function setNamespace($namespace)
	{
		$this->namespace = $namespace;
	} // end setNamespace();
	
	/**
	 * @see NamespacedElementInterface
	 */
	public function getNamespace()
	{
		return $this->namespace;
	} // end getNamespace();
	
	/**
	 * @see NamespacedElementInterface
	 */
	public function setURIIdentifier($id)
	{
		$this->uriID = (int) $id;
	} // end setURIIdentifier();
	
	/**
	 * @see NamespacedElementInterface
	 */
	public function getURIIdentifier()
	{
		return $this->uriID;
	} // end getURIIdentifier();
	
	/**
	 * @see NamespacedElementInterface
	 */
	public function getFullyQualifiedName()
	{
		if(empty($this->namespace))
		{
			return $this->name;
		}
		return $this->namespace.':'.$this->name;
	} // end getFullyQualifiedName();
	
	/**
	 * Returns an array containing all the element attributes.
	 * @return array
	 */
	public function getAttributes()
	{
		return $this->attributes;
	} // end getAttributes();
	
	public function getAttribute($name)
	{
		$name = (string)$name;
		if(!isset($this->attributes[$name]))
		{
			throw new UnknownASTElementException('The attribute "'.$name.'" has not been found.');
		}
		return $this->attributes[$name];
	} // end getAttribute();
	
	public function addAttribute(Attribute $attribute)
	{
		$name = $attribute->getFullyQualifiedName();
		if(isset($this->attributes[$name]))
		{
			throw new ASTException('Cannot add an attribute: the element "'.$this->name.'" has already got an attribute called "'.$name.'".');
		}
		$this->attributes[$name] = $attribute;
		return $this;
	} // end addAttribute();
	
	/**
	 * Removes the attribute from the element.
	 * 
	 * @param string|Attribute $attribute The attribute to remove.
	 * @return Element Fluent interface.
	 */
	public function removeAttribute($attribute)
	{
		if(is_object($attribute) && $attribute instanceof Attribute)
		{
			$name = $attribute->getFullyQualifiedName();
		}
		else
		{
			$name = (string)$attribute;
		}
		if(!isset($this->attributes[$name]))
		{
			throw new UnknownASTElementException('The attribute "'.$name.'" has not been found.');
		}
		unset($this->attributes[$name]);
		return $this;
	} // end removeAttribute();
	
	public function removeAttributes()
	{
		$this->attributes = array();
		return $this;
	} // end removeAttributes();
	
	public function hasAttributes()
	{
		return sizeof($this->attributes) > 0;
	} // end hasAttributes();
	
	/**
	 * Sets the emptiness parameter. Empty elements do not have any subnodes.
	 * 
	 * @param boolean $emptyStatus The new emptiness status
	 * @return Element Fluent interface.
	 */
	public function setEmpty($emptyStatus)
	{
		 $this->empty = (bool)$emptyStatus;
		 return $this;
	} // end setEmpty();
	
	/**
	 * Returns true, if this node is marked as empty.
	 * 
	 * @return boolean 
	 */
	public function isEmpty()
	{
		return $this->empty;
	} // end isEmpty();
	
	public function __toString()
	{
		return $this->getFullyQualifiedName();
	} // end __toString();
	
	/**
	 * Returns the list of elements within the given element that match the specified
	 * name and optionally, the namespace name. The namespace can be either a string,
	 * an integer (the URI identifier) or <tt>null</tt> (empty namespace). An asterisk
	 * indicates that we want to match any name.
	 * 
	 * @param string|int|null $namespace The matched namespace or namespace URI identifier.
	 * @param string $name The matched name.
	 * @param boolean $isRecursive Is the lookup recursive?
	 */
	public function getElementsByTagNameNS($namespace, $name, $isRecursive = true)
	{
		$queue = new SplQueue();
		$item = $this->getFirstChild();
		while(null !== $item)
		{
			$queue->enqueue($item);
			$item = $item->getNext();
		}
		$elements = array();
		
		while($queue->count() > 0)
		{
			$item = $queue->dequeue();
			if($item instanceof Element)
			{
				$ok = true;
				// Match the namespace
				if(is_integer($namespace))
				{
					if($item->getURIIdentifier() !== $namespace)
					{
						$ok = false;
					}
				}
				else
				{
					if($item->getNamespace() !== $namespace)
					{
						$ok = false;
					}
				}
				if('*' != $name)
				{
					if($item->getName() != $name)
					{
						$ok = false;
					}
				}
				
				// If everything matches, put it into the result set.
				if($ok)
				{
					$elements[] = $item;
				}
				
				if($isRecursive)
				{
					$subitem = $item->getFirstChild();
					while(null !== $subitem)
					{
						$queue->enqueue($subitem);
						$subitem = $subitem->getNext();
					}
				}
			}
		}
		return $elements;
	} // end getElementsByTagNameNS();
	
	public function getElementsByTagName($name, $isRecursive = true)
	{
		$queue = new SplQueue();
		$item = $this->getFirstChild();
		while(null !== $item)
		{
			$queue->enqueue($item);
			$item = $item->getNext();
		}
		$elements = array();
		
		while($queue->count() > 0)
		{
			$item = $queue->dequeue();
			if($item instanceof Element)
			{			
				// If everything matches, put it into the result set.
				if($item->getName() == $name)
				{
					$elements[] = $item;
				}
				
				if($isRecursive)
				{
					$subitem = $item->getFirstChild();
					while(null !== $subitem)
					{
						$queue->enqueue($subitem);
						$subitem = $subitem->getNext();
					}
				}
			}
		}
		return $elements;
	} // end getElementsByTagName();
} // end Element;