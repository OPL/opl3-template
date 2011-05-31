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
namespace Opl\Template;
use InvalidArgumentException;

/**
 * The class represents an execution context - a place where the variable
 * values and other execution settings are stored.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class Context
{
	protected $data;
	protected $objects;

	public function setVar($name, $value)
	{
		$this->data[$name] = $value;
	} // end setVar();

	public function getVar($name)
	{
		if(!isset($this->data[$name]))
		{
			return null;
		}
		return $this->data[$name];
	} // end getVar();

	/**
	 *
	 * @throws InvalidArgumentException
	 * @param string $name
	 * @param object $object
	 */
	public function setObject($name, $object)
	{
		if(!is_object($object))
		{
			throw new InvalidArgumentException('The second argument of Opl\Template\Context::setObject() must be an object.');
		}
		$this->objects[$name] = $object;
	} // end setObject();

	public function getObject($name)
	{
		if(!isset($this->objects[$name]))
		{
			return null;
		}
		return $this->objects[$name];
	} // end getObject();
} // end Context;