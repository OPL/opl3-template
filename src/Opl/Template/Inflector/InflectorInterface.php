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
namespace Opl\Template\Inflector;

/**
 * Inflectors are responsible for locating the template source and compiled
 * files in some data storage, usually a hard disk.
 */
interface InflectorInterface
{
	/** Returns the actual path to the source template suitable to use with
	 * the PHP filesystem functions.
	 *
	 * @param string $file The template file
	 * @return string
	 */
	public function getSourcePath($file);

	/**
	 * Returns the actual path to the compiled template suitable to use
	 * with the PHP filesystem functions.
	 *
	 * @param string $file The template file
	 * @param array $inheritance The dynamic template inheritance list
	 * @return string
	 */
	public function getCompiledPath($file, array $inheritance);
} // end InflectorInterface;