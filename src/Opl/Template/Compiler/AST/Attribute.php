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
 * Represents an Element node attribute.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class Attribute implements NamedElementInterface, NamespacedElementInterface
{
	private $name;
	private $namespace;
	private $value;
	
	/**
	 * Creates the Attribute with the given name. If the attribute is not assigned
	 * to any namespace, the namespace should be set to null.
	 *
	 * @throws ASTException The attribute name cannot be null.
	 * @param string|null $namespace The element attribute name or null.
	 * @param string $name The attribute name.
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
			throw new ASTException('The OPT AST attribute name cannot be empty.');
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
	public function getFullyQualifiedName()
	{
		if(empty($this->namespace))
		{
			return $this->name;
		}
		return $this->namespace.':'.$this->name;
	} // end getFullyQualifiedName();
	
	public function setValue($value)
	{
		$this->value = $value;
	} // end setValue();
	
	public function getValue()
	{
		return $this->value;
	} // end getValue();
} // end Attribute;