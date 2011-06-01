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
namespace Opl\Template\Renderer;
use Opl\Template\Compiler\CompilerFactoryInterface;
use Opl\Template\Inflector\InflectorInterface;
use Opl\Template\Inflector\ExternalInflectorInterface;
use Opl\Template\Unit;

/**
 * This renderer returns the template execution output back to the
 * script.
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class ReturnRenderer implements RendererInterface, ExternalInflectorInterface
{
	/**
	 * The inflector used by this renderer.
	 * @var Opl\Template\Inflector\InflectorInterface
	 */
	private $inflector;
	
	/**
	 * The compiler factory used by this template.
	 * @var Opl\Template\Compiler\CompilerFactoryInterface
	 */
	private $compilerFactory;
	
	/**
	 * The template execution mode.
	 * @var int
	 */
	private $executionMode = RendererInterface::EXECUTE_STANDARD;
	
	/**
	 * @see Opl\Template\Inflector\ExternalInflectorInterface
	 */
	public function setInflector(InflectorInterface $inflector)
	{
		$this->inflector = $inflector;
	} // end setInflector();
	
	/**
	 * @see Opl\Template\Inflector\ExternalInflectorInterface
	 */
	public function getInflector()
	{
		return $this->inflector;
	} // end getInflector();
	
	/**
	 * Sets the compiler factory used by this renderer. Setting the factory is not
	 * obligatory, but without it, you will not be able to compile any templates.
	 * 
	 * @param CompilerFactoryInterface $compilerFactory 
	 */
	public function setCompilerFactory(CompilerFactoryInterface $compilerFactory)
	{
		$this->compilerFactory = $compilerFactory;
	} // end setCompilerFactory();
	
	/**
	 * Returns the current compiler factory.
	 * 
	 * @return Opl\Template\Compiler\CompilerFactoryInterface 
	 */
	public function getCompilerFactory()
	{
		return $this->compilerFactory;
	} // end getCompilerFactory();
	
	/**
	 * Sets the template execution mode used by this renderer: <tt>EXECUTE_DEBUG</tt>,
	 * <tt>EXECUTE_STANDARD</tt> and <tt>EXECUTE_PERFORMANCE</tt>.
	 * 
	 * @param int $executionMode The new execution mode.
	 */
	public function setExecutionMode($executionMode)
	{
		$this->executionMode = (int)$executionMode;
	} // end setExecutionMode();
	
	/**
	 * Returns the current template execution mode.
	 * 
	 * @return int 
	 */
	public function getExecutionMode()
	{
		return $this->executionMode;
	} // end getExecutionMode();
	
	/**
	 * @see Opl\Template\Renderer\RendererInterface
	 * @return string
	 */
	public function render(Unit $unit)
	{
		ob_start();
		switch($this->executionMode)
		{
			case RendererInterface::EXECUTE_STANDARD:
				$unit->executeStandard($this->inflector, $this->compilerFactory);
				break;
			case RendererInterface::EXECUTE_PERFORMANCE:
				$unit->executePerformance($this->inflector, $this->compilerFactory);
				break;
			case RendererInterface::EXECUTE_DEBUG:
				$unit->executeDebug($this->inflector, $this->compilerFactory);
		}
		return ob_get_clean();
	} // end render();
} // end ReturnRenderer;