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
 * All the AST elements that have names must implement this interface.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
interface NamedElementInterface
{
	/**
	 * Sets the element name.
	 *
	 * @param string $name
	 */
	public function setName($name);
	/**
	 * Returns the element name.
	 *
	 * @return The element name.
	 */
	public function getName();
} // end NamedElementInterface;