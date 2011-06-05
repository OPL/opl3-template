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
namespace Opl\Template\Exception;

/**
 * Informs about detected infinite recursion in various template language
 * and compiler components. The exception allows to specify the stack showing
 * the place of the recursion.
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class InfiniteRecursionException extends CompilationException
{
	/**
	 * The stack information showing, where the infinite recursion occured.
	 * @var array
	 */
	protected $stackInfo = null;
	
	/**
	 * Sets the stack information showing the place, where the infinite recursion
	 * occured.
	 * 
	 * @param array $stackInfo The stack information.
	 * @return InfiniteRecursionException Fluent interface.
	 */
	public function setStackInfo(array $stackInfo)
	{
		$this->stackInfo = $stackInfo;
		return $this;
	} // end setStackInfo();
	
	/**
	 * Returns the stack information associated with this exception.
	 * 
	 * @return array 
	 */
	public function getStackInfo()
	{
		return $this->stackInfo;
	} // end getStackInfo();
} // end InfiniteRecursionException;