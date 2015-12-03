<?php
namespace PsrEasy\Tests\Http\Message;

use PsrEasy\Http\Message\ServerRequest;
use PsrEasy\Http\Message\UploadedFile;

class ServerRequestTest extends \PHPUnit_Framework_TestCase
{
    private $request = null;

    public function setUp()
    {
        $this->request = new ServerRequest();
    }

    public function testGetServerParams()
    {
        $this->assertEquals($_SERVER, $this->request->getServerParams());
    }

    public function testGetCoolieParams()
    {
        $this->assertEquals([], $this->request->getCookieParams());
        $_COOKIE= ['a' => 'ca', 'b' => 'cb'];
        $this->assertEquals($_COOKIE, $this->request->getCookieParams());
    }

    public function testWithCookieParams()
    {
        $cookies = ['a' => 'ca', 'b' => 'cb'];
        $this->assertEquals($this->request, $this->request->withCookieParams($cookies));
        $this->assertEquals($cookies, $this->request->getCookieParams());
    }

    public function testGetQueryParams()
    {
        $this->assertEquals([], $this->request->getQueryParams());
        $_GET = ['a' => 'ca', 'b' => 'cb'];
        $this->assertEquals($_GET, $this->request->getQueryParams());
    }

    public function testWithQueryParams()
    {
        $query = ['a' => 'query a', 'b' => 'query b'];
        $this->assertEquals($this->request, $this->request->withQueryParams($query));
        $this->assertEquals($query, $this->request->getQueryParams());
    }

    public function testGetUploadedFiles()
    {
        $this->assertEquals([], $this->request->getUploadedFiles());

        $_FILES = [
            'form' => [
                'user' => [
                    'file1' => [
                        'tmp_name' => 'phpUxcOts',
                        'name' => 'my-file1.png',
                        'size' => 90996,
                        'type' => 'image/png',
                        'error' => 0,
                    ],
                    'file2' => [
                        'tmp_name' => 'phpUxcOty',
                        'name' => 'my-file2.png',
                        'size' => 90996,
                        'type' => 'image/png',
                        'error' => 0,
                    ],
                ],
            ],
        ];
        $uploadedFiles = $this->request->getUploadedFiles();
        $this->assertArrayHasKey('form', $uploadedFiles);
        $this->assertInstanceOf('\\PsrEasy\\Http\\Message\\UploadedFile', $uploadedFiles['form']['user']['file1']);
        $this->assertInstanceOf('\\PsrEasy\\Http\\Message\\UploadedFile', $uploadedFiles['form']['user']['file2']);
    }

    public function testGetUploadedFiles2()
    {
        $_FILES = [
            'form' => [
                'user' => [
                    'files' => [
                        'tmp_name' => [
                            0 => 'phpUxcOta',
                            1 => 'phpUxcOtb',
                            2 => 'phpUxcOtc',
                        ],
                        'name' => [
                            0 => 'file1.txt',
                            1 => 'file2.txt',
                            2 => 'file3.txt',
                        ],
                        'size' => [
                            0 => 123,
                            1 => 125,
                            2 => 222,
                        ],
                        'error' => [
                            0 => 0,
                            1 => 0,
                            2 => 0,
                        ],
                        'type' => [
                            0 => 'text/plain',
                            1 => 'text/plain',
                            2 => 'text/plain',
                        ],
                    ],
                ],
            ],
        ];
        $uploadedFiles = $this->request->getUploadedFiles();
        $this->assertArrayHasKey('form', $uploadedFiles);
        $this->assertInstanceOf('\\PsrEasy\\Http\\Message\\UploadedFile', $uploadedFiles['form']['user']['files'][0]);
        $this->assertInstanceOf('\\PsrEasy\\Http\\Message\\UploadedFile', $uploadedFiles['form']['user']['files'][1]);
        $this->assertInstanceOf('\\PsrEasy\\Http\\Message\\UploadedFile', $uploadedFiles['form']['user']['files'][2]);
    }

    public function testWithUploadedFiles()
    {
        $fileInfo = [
            'tmp_name' => 'phpUxcOty',
            'name'     => 'my-avatar.png',
            'size'     => 90996,
            'type'     => 'image/png',
            'error'    => 0,
        ];
        $files = [
            'form' => [
                'user' => [
                    'avatar' => new UploadedFile($fileInfo),
                ],
            ],
        ];
        $this->assertEquals($this->request, $this->request->withUploadedFiles($files));
        $this->setExpectedException('\InvalidArgumentException');

        $files = [
            'form' => [
                'user' => [
                    'avatar' => $fileInfo,
                ],
            ],
        ];
        $this->assertEquals($this->request, $this->request->withUploadedFiles($files));
    }

    public function testGetParsedBody()
    {
        $this->assertNull($this->request->getParsedBody());
        $this->request->withMethod('POST');
        $_POST = ['a' => 'pa', 'b' => 'pb'];
        $this->assertEquals($_POST, $this->request->getParsedBody());
    }

    public function testWithParsedBody()
    {
        $body = 'this is a test body';
        $this->assertEquals($this->request, $this->request->withParsedBody($body));
    }

    public function testGetAttributes()
    {
        $this->assertEquals([], $this->request->getAttributes());
    }

    public function testGetAttribute()
    {
        $this->assertNull($this->request->getAttribute('a1'));
        $this->assertEquals('v1', $this->request->getAttribute('a1', 'v1'));
        $this->assertNull($this->request->getAttribute('a1'));
    }

    public function testWithAttribute()
    {
        $this->assertEquals($this->request, $this->request->withAttribute('a1', 'v1'));
        $this->assertEquals('v1', $this->request->getAttribute('a1'));
    }

    public function testWithoutAttribute()
    {
        $this->assertEquals($this->request, $this->request->withAttribute('a1', 'v1'));
        $this->assertEquals('v1', $this->request->getAttribute('a1'));
        $this->assertEquals($this->request, $this->request->withoutAttribute('a1'));
        $this->assertNull($this->request->getAttribute('a1'));
    }
}
