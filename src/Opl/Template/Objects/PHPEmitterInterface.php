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
namespace Opl\Template\Objects;

/**
 * This interface allows the objects to emit plain, direct calls of PHP
 * functions directly in the template compiled code which optimizes the
 * execution.
 */
interface PHPEmitterInterface
{
	/**
	 * Returns the function prototype description for the template compiler,
	 * so that it can be used to produce the compiled code. The method name
	 * is prefixed with an underscore, which prevents it from getting called
	 * within the template.
	 *
	 * The method must return null, if it cannot handle the specified function.
	 *
	 * @param string $name The function name.
	 * @return string|null
	 */
	public function _getFunctionPrototype($name);
} // end PHPEmitterInterface;