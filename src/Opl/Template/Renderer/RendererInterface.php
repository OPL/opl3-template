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
use Opl\Template\Unit;

/**
 * An interface for writing various template renderers. Their task is
 * to send the template output somewhere.
 */
interface RendererInterface
{
	const EXECUTE_DEBUG = 0;
	const EXECUTE_STANDARD = 1;
	const EXECUTE_PERFORMANCE = 2;
	
	/**
	 * Renders the template into some output.
	 *
	 * @param Unit $unit The template unit to execute.
	 */
	public function render(Unit $unit);
} // end RendererInterface;
