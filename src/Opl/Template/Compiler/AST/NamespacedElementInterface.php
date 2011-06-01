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
 * All the AST elements that support namespaces must implement this interface.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
interface NamespacedElementInterface
{
	/**
	 * Sets the element namespace.
	 *
	 * @param string $namespace
	 */
	public function setNamespace($namespace);
	/**
	 * Returns the element namespace.
	 *
	 * @return The element namespace.
	 */
	public function getNamespace();
	
	/**
	 * Sets the numerical identifier of the namespace URI this element
	 * belongs to.
	 * 
	 * @param int $id
	 */
	public function setURIIdentifier($id);
	
	/**
	 * Returns the numerical namespace URI identifier.
	 * 
	 * @return int
	 */
	public function getURIIdentifier();
	
	/**
	 * Returns the fully qualified element name, with the prepended namespace.
	 * The namespace should be separated from the name with a colon.
	 *
	 * @return The namespace and the name separated with a colon.
	 */
	public function getFullyQualifiedName();
} // end NamespacedElementInterface;