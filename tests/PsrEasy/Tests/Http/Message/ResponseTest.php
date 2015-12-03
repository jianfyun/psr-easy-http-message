<?php
namespace PsrEasy\Tests\Http\Message;

use PsrEasy\Http\Message\Response;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    private $response = null;

    public function setUp()
    {
        $this->response = new Response();
    }

    public function testGetStatusCode()
    {
        $this->assertEquals(200, $this->response->getStatusCode());
    }

    public function testWithStatus()
    {
        $status = 400;
        $phrase = 'Bad Request';
        $this->assertEquals($this->response, $this->response->withStatus($status));
        $this->assertEquals(400, $this->response->getStatusCode());
        $this->assertEquals($phrase, $this->response->getReasonPhrase());
    }

    public function getReasonPhrase()
    {
        $this->assertEquals('', $this->response->getReasonPhrase());
        $status = 400;
        $phrase = 'Bad Request';
        $this->assertEquals($this->response, $this->response->withStatus($status, $phrase));
        $this->assertEquals($phrase, $this->response->getReasonPhrase());
    }
}
