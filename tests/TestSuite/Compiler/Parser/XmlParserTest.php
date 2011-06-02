<?php
/**
 * Unit tests for Open Power Template
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
namespace TestSuite\Compiler\Parser;
use Opl\Template\Compiler\AST\Element;
use Opl\Template\Compiler\AST\Attribute;
use Opl\Template\Compiler\Parser\XmlParser;

/**
 * @covers \Opl\Template\Compiler\Parser\XmlParser
 * @runTestsInSeparateProcesses
 */
class XmlParserTest extends \PHPUnit_Framework_TestCase
{
	public function testSetDefaultExpressionType()
	{
		$parser = new XmlParser();
		$parser->setDefaultExpressionType('foo');
		$this->assertEquals('foo', $parser->getDefaultExpressionType());
	} // end testSetDefaultExpressionType();
	
	public function testExtractAttributesExtractsRequiredAttribute()
	{
		$parser = new XmlParser();
		
		$element = new Element('foo', 'bar');
		$attr = new Attribute(null, 'attr1');
		$attr->setValue('foo');
		$element->addAttribute($attr);
		
		$params = array(
			'attr1' => array(XmlParser::REQUIRED, XmlParser::ATTR_ID)			
		);
		$parser->extractAttributes($element, $params);

		$this->assertEquals('foo', $params['attr1']);
	} // end testExtractAttributesExtractsRequiredAttribute();
	
	/**
	 * @expectedException Opl\Template\Exception\AttributeExtractionException
	 */
	public function testExtractAttributesThrowsAnExceptionIfRequiredAttributeIsNotDefined()
	{
		$parser = new XmlParser();
		
		$element = new Element('foo', 'bar');
		
		$params = array(
			'attr1' => array(XmlParser::REQUIRED, XmlParser::ATTR_ID)			
		);
		$parser->extractAttributes($element, $params);
	} // end testExtractAttributesThrowsAnExceptionIfRequiredAttributeIsNotDefined();
	
	public function testExtractAttributesExtractsOptionalAttribute()
	{
		$parser = new XmlParser();
		
		$element = new Element('foo', 'bar');
		$attr = new Attribute(null, 'attr1');
		$attr->setValue('foo');
		$element->addAttribute($attr);
		
		$params = array(
			'attr1' => array(XmlParser::OPTIONAL, XmlParser::ATTR_ID, 'bar')			
		);
		$parser->extractAttributes($element, $params);

		$this->assertEquals('foo', $params['attr1']);
	} // end testExtractAttributesExtractsOptionalAttribute();
	
	public function testExtractAttributesSetsDefaultValueIfOptionalAttributeIsNotDefined()
	{
		$parser = new XmlParser();
		
		$element = new Element('foo', 'bar');
	
		$params = array(
			'attr1' => array(XmlParser::OPTIONAL, XmlParser::ATTR_ID, 'bar')			
		);
		$parser->extractAttributes($element, $params);

		$this->assertEquals('bar', $params['attr1']);
	} // end testExtractAttributesSetsDefaultValueIfOptionalAttributeIsNotDefined();
	
	public function testExtractAttributesExtractsUnknownAttributes()
	{
		$parser = new XmlParser();
		
		$element = new Element('foo', 'bar');
		$attr = new Attribute(null, 'attr1');
		$attr->setValue('foo');
		$element->addAttribute($attr);
		$attr = new Attribute(null, 'attr2');
		$attr->setValue('bar');
		$element->addAttribute($attr);
	
		$params = array(
			'attr1' => array(XmlParser::REQUIRED, XmlParser::ATTR_ID, 'bar'),
			'__UNKNOWN__' => array(XmlParser::OPTIONAL, XmlParser::ATTR_ID)
		);
		$unknown = $parser->extractAttributes($element, $params);

		$this->assertEquals(1, sizeof($unknown));
		$this->assertEquals('bar', $unknown['attr2']);
		$this->assertEquals('foo', $params['attr1']);
	} // end testExtractAttributesExtractsUnknownAttributes();

	public function testExtractAttributesIgnoresSpecialAttributes()
	{
		$parser = new XmlParser();
		
		$element = new Element('foo', 'bar');
		$attr = new Attribute(null, 'attr1');
		$attr->setValue('foo');
		$element->addAttribute($attr);
		$attr = new Attribute(null, 'attr2');
		$attr->setURIIdentifier(1);
		$attr->setValue('bar');
		$element->addAttribute($attr);
	
		$params = array(
			'attr1' => array(XmlParser::OPTIONAL, XmlParser::ATTR_ID, 'bar'),
			'attr2' => array(XmlParser::OPTIONAL, XmlParser::ATTR_ID, 'joe'),
		);
		$parser->extractAttributes($element, $params);

		$this->assertEquals('foo', $params['attr1']);
		$this->assertEquals('joe', $params['attr2']);
	} // end testExtractAttributesIgnoresSpecialAttributes();
	
	public function identifierDataProvider()
	{
		return array(
			array('foo123', true),
			array('123foo', false),
			array('_123', true),
			array('123_', false),
			array('FOO123', true),
			array('foo bar', false)
		);
	} // end identifierDataProvider();
	
	/**
	 * @dataProvider identifierDataProvider
	 */
	public function testExtractAttributesTestIdentifiers($identifier)
	{
		$parser = new XmlParser();
		
		$element = new Element('foo', 'bar');
		$attr = new Attribute(null, 'attr1');
		$attr->setValue($identifier[0]);
		$element->addAttribute($attr);
	
		try
		{
			$params = array(
				'attr1' => array(XmlParser::REQUIRED, XmlParser::ATTR_ID),
			);
			$parser->extractAttributes($element, $params);
			if($identifier[1])
			{
				return true;
			}
			$this->fail('This identifier should fail on the identifier test: '.$identifier[0]);
		}
		catch(\Exception $exception)
		{
			if(!$identifier[1])
			{
				return true;
			}
			$this->fail('This identifier should not fail on the identifier test: '.$identifier[0]);
		}
	} // end testExtractAttributesTestInvalidIdentifier1();
} // end XmlParserTest;