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
namespace Opl\Template\Compiler\Debugger;
use Opl\Template\Compiler\AST\Node;
use Opl\Template\Compiler\AST\Scannable;

/**
 * An utility class to render the XML Abstract Syntax Tree.
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class XmlDebugger
{
	public static function dump(Node $node)
	{
		if($node instanceof Scannable)
		{
			echo '<ul>';
			
			foreach($node as $item)
			{
				switch(get_class($item))
				{
					case 'Opl\Template\Compiler\AST\Element':
						echo '<li>';
						if($item->isVisible())
						{
							echo '<strong>'.$item->getFullyQualifiedName().'</strong>';
						}
						else
						{
							echo $item->getFullyQualifiedName();
						}
						self::dump($item);
						echo '</li>';
						break;
					case 'Opl\Template\Compiler\AST\Text':
						echo '<li>';
						if($item->isVisible())
						{
							echo '<strong>##TEXT##</strong>';
						}
						else
						{
							echo '##TEXT##';
						}
						self::dump($item);
						echo '</li>';
						break;
					case 'Opl\Template\Compiler\AST\Cdata':
						echo '<li>';
						if($item->isVisible())
						{
							echo '<strong>##CDATA:</strong> '.(string)$item;
						}
						else
						{
							echo '##CDATA: '.(string)$item;
						}
						echo '</li>';
						break;
				}
			}
			echo '</ul>';
		}
	} // end dump();
} // end XmlDebugger;