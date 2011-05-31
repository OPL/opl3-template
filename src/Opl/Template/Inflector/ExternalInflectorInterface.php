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
use Opl\Template\Inflector\InflectorInterface;

/**
 * This interface indicates that the template unit renderer does not
 * create its own inflector, but rather allows to create it externally
 * and inject.
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
interface ExternalInflectorInterface
{
	public function setInflector(InflectorInterface $inflector);
	public function getInflector();
} // end ExternalInflectorInterface;