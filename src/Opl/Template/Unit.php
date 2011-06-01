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
use Opl\Template\Compiler\CompilerFactoryInterface;
use Opl\Template\Exception\MissingTemplateException;
use Opl\Template\Inflector\InflectorInterface;

/**
 * The execution units combine the templates with the data they should
 * be populated with. Units should be obtained through the unit builders.
 *
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class Unit extends Context
{
	/**
	 * The template name
	 * @var string
	 */
	protected $templateName;
	/**
	 * The context with global data.
	 * @var Opl\Template\Context
	 */
	protected $globalContext = null;

	/**
	 * Creates the new unit for the given template.
	 * 
	 * @param string $templateName The template name
	 */
	public function __construct($templateName)
	{
		$this->templateName = (string)$templateName;
	} // end __construct();

	/**
	 * Returns the template name this unit is associated to.
	 * @return string
	 */
	public function getTemplateName()
	{
		return $this->templateName;
	} // end getTemplateName();

	/**
	 * Sets the global context that allows to share a common set of variables
	 * between different units.
	 * 
	 * @param Context $globalContext The new global context.
	 */
	public function setGlobalContext(Context $globalContext)
	{
		$this->globalContext = $globalContext;
	} // end setGlobalContext();

	/**
	 * Returns the current global context.
	 * 
	 * @return string 
	 */
	public function getGlobalContext()
	{
		return $this->globalContext;
	} // end getGlobalContext();

	/**
	 * The debug execution of the template, typically for testing the compiler
	 * and implementing new instructions/languages, where we need to check the
	 * effects immediately.
	 *
	 * @param InflectorInterface $inflector
	 * @param CompilerFactoryInterface $compilerFactory
	 */
	public function executeDebug(InflectorInterface $inflector, CompilerFactoryInterface $compilerFactory)
	{
		$sourceName = $inflector->getSourcePath($this->templateName);
		$compiledName = $inflector->getCompiledPath($this->templateName, array());

		if(!file_exists($sourceName))
		{
			throw new MissingTemplateException('The template \''.$this->templateName.'\' does not exist.');
		}

		$compiler = $compilerFactory->getCompiler();
	//	$compiler->setCompiledUnit($this);
		$compiler->compile($sourceName, $compiledName, $inflector);

		require($compiledName);
	} // end excuteDebug();

	/**
	 * The standard template execution. If the source version has been modified,
	 * we recompile it. Otherwise we load the compiled version.
	 *
	 * @param InflectorInterface $inflector
	 * @param CompilerFactoryInterface $compilerFactory
	 */
	public function executeStandard(InflectorInterface $inflector, CompilerFactoryInterface $compilerFactory)
	{
		$sourceName = $inflector->getSourcePath($this->templateName);
		$compiledName = $inflector->getCompiledPath($this->templateName, array());
		
		$sourceModificationTime = @filemtime($sourceName);
		$compiledModificationTime = @filemtime($compiledName);

		if(false === $sourceModificationTime)
		{
			throw new MissingTemplateException('The template \''.$this->templateName.'\' does not exist.');
		}
		if(false === $compiledModificationTime || $sourceModificationTime > $compiledModificationTime)
		{
			$compiler = $compilerFactory->getCompiler();
		//	$compiler->setCompiledUnit($this);
			$compiler->compile($sourceName, $compiledName, $inflector);
		}

		require($compiledName);
	} // end executeStandard();

	/**
	 * The fast template execution. We do not recompile the template, and do not
	 * even check whether the source version exists. We focus on the compiled version
	 * only.
	 *
	 * @param InflectorInterface $inflector
	 */
	public function executePerformance(InflectorInterface $inflector)
	{
		$compiledName = $inflector->getCompiledPath($this->templateName, array());

		if(!file_exists($compiledName))
		{
			throw new MissingTemplateException('The template \''.$this->templateName.'\' does not exist.');
		}
		require($compiledName);
	} // end executePerformance();
} // end Unit;