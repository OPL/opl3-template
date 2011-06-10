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
namespace Opl\Template\Compiler\AST;

/**
 * A modification of the original Document class that allows to detect the
 * root element of the document.
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class XmlDocument extends Document
{
	/**
	 * The root element.
	 * @var Element
	 */
	private $rootElement;

	/**
	 * Returns the current root element of the document.
	 * 
	 * @return Element
	 */
	public function getRootElement()
	{
		return $this->rootElement;
	} // end getRootNode();
	
	/**
	 * @see Scannable
	 */
	protected function isChildTypeAllowed(Node $node)
	{
		if($node instanceof Element)
		{
			if(null !== $this->rootElement)
			{
				throw new ASTException('Cannot add another root node to the document. The problematic node is: \''.$node->getFullyQualifiedName().'\'.');
			}
			else
			{
				$this->rootElement = $node;
			}
		}
	} // end isChildTypeAllowed();
} // end XmlDocument;