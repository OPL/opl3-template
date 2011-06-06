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

/**
 * This is the standard unit factory implementation that provides a basic
 * support for managing the global context shared across all the units.
 * Note that in a real system you would be rather interested in writing
 * your own unit factory using the provided interface.
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class UnitFactory implements UnitFactoryInterface
{
	/**
	 * The context for keeping global variables etc.
	 * @var Context
	 */
	protected $globalContext;

	/**
	 * Returns and lazy-constructs the global context used by all the units
	 * created with this factory.
	 * 
	 * @return Context
	 */
	public function getGlobalContext()
	{
		if(null === $this->globalContext)
		{
			$this->globalContext = new Context();
		}
		return $this->globalContext;
	} // end getGlobalContext();
	
	/**
	 * @see UnitFactoryInterface
	 */
	public function createUnit($template)
	{
		$unit = new Unit($template);
		$unit->setGlobalContext($this->getGlobalContext());
		return $unit;
	} // end createUnit();
} // end UnitFactory;