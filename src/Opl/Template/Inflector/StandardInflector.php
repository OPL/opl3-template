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

class StandardInflector implements InflectorInterface
{
	protected $allowRelativePaths = false;
	protected $compileDir;
	protected $streams = array();

	/**
	 * Creates the inflector and initializes it with the default source path,
	 * and the compilation directory path. The source directory is registered
	 * as a 'default' stream.
	 *
	 * @param string $sourceDir The source directory
	 * @param string $compileDir The compilation directory
	 */
	public function __construct($sourceDir, $compileDir)
	{
		$this->streams['default'] = $sourceDir;
		$this->compileDir = $compileDir;
	} // end __construct();

	/**
	 * Enables or disables the use of relative paths in the template names.
	 * By default, this option is turned off.
	 * 
	 * @param boolean $status 
	 */
	public function setAllowRelativePaths($status)
	{
		$this->allowRelativePaths = (boolean)$status;
	} // end setAllowRelativePaths();

	/**
	 * Returns the <tt>allow relative paths</tt> status.
	 * 
	 * @return boolean 
	 */
	public function getAllowRelativePaths()
	{
		return $this->allowRelativePaths;
	} // end getAllowRelativePaths();
	
	/**
	 * Returns the path to the compilation directory.
	 * @return string 
	 */
	public function getCompileDir()
	{
		return $this->compileDir;
	} // end getCompileDir();

	/**
	 *
	 * @param string $name The stream name.
	 * @param string $path The path represented by the stream.
	 * @param boolean $isSecure Is the path ended with the trailing slash.
	 */
	public function addStream($name, $path, $isSecure = true)
	{
		if(isset($this->streams[$name]))
		{
			throw new InflectorException('The stream name \''.$name.'\' is already in use.');
		}
		if($isSecure)
		{
			$length = strlen($path);
			if(0 == $length || $path[$length - 1] != '/')
			{
				$path .= '/';
			}
		}
		$this->streams[$name] = $path;
	} // end addStream();

	public function hasStream($name)
	{
		return isset($this->streams[$name]);
	} // end hasStream();

	public function getStream($name)
	{
		if(!isset($this->streams[$name]))
		{
			throw new InflectorException('The stream name \''.$name.'\' is not registered.');
		}
		return $this->streams[$name];
	} // end getStream();

	public function removeStream($name)
	{
		if(!isset($this->streams[$name]))
		{
			throw new InflectorException('The stream name \''.$name.'\' is not registered.');
		}
		unset($this->streams[$name]);
	} // end removeStream();

	/**
	 * @see InflectorInterface
	 */
	public function getSourcePath($path)
	{
		$stream = 'default';
		if(false !== strpos($path, ':'))
		{
			list($stream, $path) = explode(':', $path);
		}
		if(!isset($this->streams[$stream]))
		{
			throw new InflectorException('Cannot load \''.$path.'\': the stream \''.$stream.'\' does not exist.');
		}
		if(!$this->allowRelativePaths && false !== strpos($path, '../'))
		{
			throw new InflectorException('Cannot load \''.$path.'\': relative paths are not allowed.');
		}
		return $this->streams[$stream].$path;
	} // end getSourcePath();

	/**
	 * @see InflectorInterface
	 */
	public function getCompiledPath($file, array $inheritance)
	{
		if(sizeof($inheritance) > 0)
		{
			$list = $inheritance;
			sort($list);
		}
		else
		{
			$list = array();
		}
		$path = '';
		foreach($list as $item)
		{
			$path .= strtr($item, '/:\\', '___').'/';
		}
		return $this->compileDir.$path.strtr((string)$file, '/:\\', '___').'.php';
	} // end getCompiledPath();
} // end StandardInflector;