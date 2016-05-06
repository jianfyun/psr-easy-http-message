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
        $this->assertEquals(Response::HTTP_OK, $this->response->getStatusCode());
    }

    public function testWithStatus()
    {
        $status = Response::HTTP_BAD_REQUEST;
        $phrase = 'Bad Request';
        $this->assertEquals($this->response, $this->response->withStatus($status));
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->response->getStatusCode());
        $this->assertEquals($phrase, $this->response->getReasonPhrase());
    }

    public function getReasonPhrase()
    {
        $this->assertEquals('', $this->response->getReasonPhrase());
        $status = Response::HTTP_BAD_REQUEST;
        $phrase = 'Bad Request';
        $this->assertEquals($this->response, $this->response->withStatus($status, $phrase));
        $this->assertEquals($phrase, $this->response->getReasonPhrase());
    }
}
