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
 * The objects of the class store various information about the current
 * unit compilation. It is clearly separated from the compiler itself
 * which allows us to reduce the mess.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class CompiledUnit
{
	/**
	 * The property manager for the nodes.
	 * @var NodePropertyManager 
	 */
	protected $propertyManager;
	
	/**
	 * The code buffer manager for the nodes.
	 * @var NodePropertyManager 
	 */
	protected $codeBufferManager;
	
	public function __construct()
	{
		// We do not lazy-initialize these fields, because we can be 100% sure
		// that they WILL be needed in any non-trivial template.
		$this->propertyManager = new NodePropertyManager('Opl\\Template\\Compiler\\PropertyCollection');
		$this->codeBufferManager = new NodePropertyManager('Opl\\Template\\Compiler\\CodeBufferCollection');
	} // end __construct();
	
	/**
	 * Returns the property manager.
	 * 
	 * @return NodePropertyManager 
	 */
	public function getPropertyManager()
	{
		return $this->propertyManager;
	} // end getPropertyManager();
	
	/**
	 * Returns the code buffer manager.
	 * 
	 * @return NodePropertyManager 
	 */
	public function getCodeBufferManager()
	{
		return $this->codeBufferManager;
	} // end getCodeBufferManager();
	
	/**
	 *
	 * @todo Implement
	 * @param type $dependency 
	 */
	public function addDependency($dependency)
	{
		
	} // end addDependency();
	
	public function getDependencyNumber()
	{
		return 0;
	} // end getDependencyNumber();
	
	public function dispose()
	{
		
	} // end dispose();
} // end CompiledUnit;