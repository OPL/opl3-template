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
namespace Opl\Template\Compiler\Instruction;
use Opl\Template\Compiler\AST\Document;

/**
 * This interface allows the instruction to register as an inheritance
 * processing hook in the <tt>inheritance</tt> stage.
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
interface InheritanceHookInterface
{
	/**
	 * The method checks, if the given document extends or includes some
	 * other template during the compilation time. It returns an array
	 * of the included templates.
	 * 
	 * @param Document $document The document to scan.
	 * @return array
	 */
	public function checkInheritance(Document $document);
} // end InheritanceHookInterface;