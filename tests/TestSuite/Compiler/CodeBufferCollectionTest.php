<?php
/**
 * Unit tests for Open Power Template
 *
 * @author Tomasz "Zyx" Jędrzejewski
 * @copyright Copyright (c) 2009 Invenzzia Group
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
namespace TestSuite\Compiler;
use Opl\Template\Compiler\CodeBufferCollection;

/**
 * @covers \Opl\Template\Compiler\CodeBufferCollection
 * @runTestsInSeparateProcesses
 */
class CodeBufferCollectionTest extends \PHPUnit_Framework_TestCase
{
	public function testGetBufferReturnsEmptyStringForEmptyBuffer()
	{
		$collection = new CodeBufferCollection();
		$this->assertEquals('', $collection->getBuffer(CodeBufferCollection::TAG_BEFORE));
	} // end testGetBufferReturnsEmptyStringForEmptyBuffer();
	
	public function testAppendAddsCodeAtTheEndOfBuffer()
	{
		$collection = new CodeBufferCollection();
		$this->assertEquals('', $collection->getBuffer(CodeBufferCollection::TAG_BEFORE));
		$collection->append(CodeBufferCollection::TAG_BEFORE, 'foo');
		$this->assertEquals(' foo', $collection->getBuffer(CodeBufferCollection::TAG_BEFORE));
		$collection->append(CodeBufferCollection::TAG_BEFORE, 'bar');
		$this->assertEquals(' foo bar', $collection->getBuffer(CodeBufferCollection::TAG_BEFORE));
	} // end testAppendAddsCodeAtTheEndOfBuffer();
	
	public function testPrependAddsCodeAtTheBeginningOfBuffer()
	{
		$collection = new CodeBufferCollection();
		$this->assertEquals('', $collection->getBuffer(CodeBufferCollection::TAG_BEFORE));
		$collection->prepend(CodeBufferCollection::TAG_BEFORE, 'foo');
		$this->assertEquals('foo ', $collection->getBuffer(CodeBufferCollection::TAG_BEFORE));
		$collection->prepend(CodeBufferCollection::TAG_BEFORE, 'bar');
		$this->assertEquals('bar foo ', $collection->getBuffer(CodeBufferCollection::TAG_BEFORE));
	} // end testPrependAddsCodeAtTheEndOfBuffer();
	
	public function testHasContentChecksIfBufferIsNotEmpty()
	{
		$collection = new CodeBufferCollection();
		$this->assertFalse($collection->hasContent(CodeBufferCollection::TAG_BEFORE));
		$collection->append(CodeBufferCollection::TAG_BEFORE, 'foo');
		$this->assertTrue($collection->hasContent(CodeBufferCollection::TAG_BEFORE));
	} // end testHasContentChecksIfBufferIsNotEmpty();
	
	public function testCopyCopiesTheBufferContentBetweenCollections()
	{
		$collection1 = new CodeBufferCollection();
		$collection1->append(CodeBufferCollection::TAG_BEFORE, 'foo');
		
		$collection2 = new CodeBufferCollection();
		$this->assertFalse($collection2->hasContent(CodeBufferCollection::TAG_BEFORE));
		
		$collection2->copy($collection1, CodeBufferCollection::TAG_BEFORE, CodeBufferCollection::TAG_BEFORE);
		$this->assertEquals(' foo', $collection2->getBuffer(CodeBufferCollection::TAG_BEFORE));
	} // end testCopyCopiesTheBufferContentBetweenCollections();
	
	public function testCopyMergesTheBuffers()
	{
		$collection1 = new CodeBufferCollection();
		$collection1->append(CodeBufferCollection::TAG_BEFORE, 'foo');
		
		$collection2 = new CodeBufferCollection();
		$collection2->append(CodeBufferCollection::TAG_BEFORE, 'bar');
		
		$collection2->copy($collection1, CodeBufferCollection::TAG_BEFORE, CodeBufferCollection::TAG_BEFORE);
		$this->assertEquals(' bar  foo', $collection2->getBuffer(CodeBufferCollection::TAG_BEFORE));
	} // end testCopyCopiesTheBufferContentBetweenCollections();
} // end CodeBufferCollectionTest;