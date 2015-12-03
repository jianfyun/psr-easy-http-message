<?php
namespace PsrEasy\Tests\Http\Message;

use PsrEasy\Http\Message\Request;
use PsrEasy\Http\Message\Uri;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    private $request = null;

    public function setUp()
    {
        $this->request = new Request();
    }

    public function testGetRequestTarget()
    {
        $this->assertEquals('/', $this->request->getRequestTarget());
    }

    public function testWithRequestTarget()
    {
        $target = '/path/action.php?a=abc&b=efg#aa';
        $this->assertEquals($this->request, $this->request->withRequestTarget($target));
        $this->assertEquals($target, $this->request->getRequestTarget());
    }

    public function testGetMethod()
    {
        $this->assertEquals('', $this->request->getMethod());
    }

    public function testWithMethod()
    {
        $this->assertEquals($this->request, $this->request->withMethod('GET'));
        $this->assertEquals('GET', $this->request->getMethod());
        $this->setExpectedException('InvalidArgumentException');
        $this->assertEquals($this->request, $this->request->withMethod('OTHER'));
    }

    public function testGetUri()
    {
        $this->assertNull($this->request->getUri());
    }

    public function testWithUri()
    {
        $uri = 'https://abc.com/ss.php';
        $this->assertEquals($this->request, $this->request->withUri(new Uri($uri)));
        $this->assertEquals($uri, (string) $this->request->getUri());
        $this->assertEquals('abc.com', $this->request->getHeaderLine('Host'));

        $uri = 'https://abc2.com/ss.php';
        $this->assertEquals($this->request, $this->request->withUri(new Uri($uri), true));
        $this->assertEquals($uri, (string) $this->request->getUri());
        $this->assertEquals('abc.com', $this->request->getHeaderLine('Host'));
    }
}
