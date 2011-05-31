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
 * Keeps the properties for a single node.
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class PropertyCollection
{
	/**
	 * The property list.
	 * @var array
	 */
	protected $properties = array();
	
	/**
	 * Returns the property value. If the property is not defined, NULL is
	 * returned.
	 * 
	 * @param string $name The property name
	 * @return mixed
	 */
	public function get($name)
	{
		$name = (string)$name;
		if(!isset($this->properties[$name]))
		{
			return null;
		}
		return $this->properties[$name];
	} // end get();
	
	/**
	 * Sets the property value.
	 * 
	 * @param string $name The property name.
	 * @param mixed $value The property value.
	 * @return PropertyCollection Fluent interface.
	 */
	public function set($name, $value)
	{
		$this->properties[(string)$name] = $value;
		return $this;
	} // end set();
	
	/**
	 * Frees the memory.
	 */
	public function dispose()
	{
		$this->buffers = null;
	} // end dispose();
} // end PropertyCollection;