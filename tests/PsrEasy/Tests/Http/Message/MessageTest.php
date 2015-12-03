<?php
namespace PsrEasy\Tests\Http\Message;

use PsrEasy\Http\Message\Message;
use PsrEasy\Http\Message\Stream;

class MessageTest extends \PHPUnit_Framework_TestCase
{
    private $message = null;

    public function setUp()
    {
        $this->message = new Message();
    }

    public function testGetProtocolVersion()
    {
        $this->assertEquals('', $this->message->getProtocolVersion());
    }

    public function testWithProtocolVersion()
    {
        $this->assertEquals($this->message, $this->message->withProtocolVersion('3.0'));
        $this->assertEquals('', $this->message->getProtocolVersion());
        $this->assertEquals($this->message, $this->message->withProtocolVersion('1.1'));
        $this->assertEquals('1.1', $this->message->getProtocolVersion());
    }

    public function testGetHeaders()
    {
        $this->assertEquals([], $this->message->getHeaders());
        $this->assertEquals($this->message, $this->message->withHeader('Accept', 'text/html'));
        $this->assertArrayHasKey('Accept', $this->message->getHeaders());
    }

    public function testHasHeader()
    {
        $this->assertFalse($this->message->hasHeader('Accept'));
        $this->assertEquals($this->message, $this->message->withHeader('Accept', 'text/html'));
        $this->assertTrue($this->message->hasHeader('Accept'));
        $this->assertTrue($this->message->hasHeader('accept'));
    }

    public function testGetHeader()
    {
        $this->assertEquals([], $this->message->getHeader('Accept'));
        $this->assertEquals($this->message, $this->message->withHeader('Accept', 'text/html'));
        $this->assertEquals(['text/html'], $this->message->getHeader('Accept'));
        $this->assertEquals(['text/html'], $this->message->getHeader('accept'));
    }

    public function testGetHeaderLine()
    {
        $this->assertEquals([], $this->message->getHeader('Accept'));
        $header = ['text/html', 'application/xhtml+xml'];
        $this->assertEquals($this->message, $this->message->withHeader('Accept', $header));
        $this->assertEquals('text/html,application/xhtml+xml', $this->message->getHeaderLine('Accept'));
        $this->assertEquals('text/html,application/xhtml+xml', $this->message->getHeaderLine('accept'));
    }

    public function testWithHeader()
    {
        $this->assertEquals($this->message, $this->message->withHeader('Accept', 'text/html'));
        $this->assertEquals(['text/html'], $this->message->getHeader('Accept'));
        $this->assertEquals(['text/html'], $this->message->getHeader('accept'));

        $header = ['text/html', 'application/xhtml+xml'];
        $this->assertEquals($this->message, $this->message->withHeader('Accept', $header));
        $this->assertEquals('text/html,application/xhtml+xml', $this->message->getHeaderLine('Accept'));
        $this->assertEquals('text/html,application/xhtml+xml', $this->message->getHeaderLine('accept'));
    }

    public function testWithAddedHeader()
    {
        $this->assertEquals($this->message, $this->message->withAddedHeader('Accept', 'text/html'));
        $this->assertEquals(['text/html'], $this->message->getHeader('Accept'));
        $this->assertEquals(['text/html'], $this->message->getHeader('accept'));

        $this->assertEquals($this->message, $this->message->withAddedHeader('Accept', 'application/xhtml+xml'));
        $this->assertEquals('text/html,application/xhtml+xml', $this->message->getHeaderLine('Accept'));
        $this->assertEquals('text/html,application/xhtml+xml', $this->message->getHeaderLine('accept'));
    }

    public function testWithoutHeader()
    {
        $header = ['text/html', 'application/xhtml+xml'];
        $this->assertEquals($this->message, $this->message->withHeader('Accept', $header));
        $this->assertEquals('text/html,application/xhtml+xml', $this->message->getHeaderLine('Accept'));
        $this->assertEquals('text/html,application/xhtml+xml', $this->message->getHeaderLine('accept'));
        $this->assertEquals($this->message, $this->message->withoutHeader('Accept'));
        $this->assertEquals([], $this->message->getHeader('Accept'));
        $this->assertEquals([], $this->message->getHeader('accept'));
    }

    public function testGetBody()
    {
        $this->assertNull($this->message->getBody());
    }

    public function testWithBody()
    {
        $body = new Stream('php://input', 'r');
        $this->assertEquals($this->message, $this->message->withBody($body));
        $this->assertInstanceOf('\\Psr\\Http\\Message\\StreamInterface', $this->message->getBody());
    }
}
