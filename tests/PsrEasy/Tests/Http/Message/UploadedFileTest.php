<?php
namespace PsrEasy\Tests\Http\Message;

use PsrEasy\Http\Message\UploadedFile;

class UploadedFileTest extends \PHPUnit_Framework_TestCase
{
    private $fileInfo = [];

    private $uploaded = null;

    public function setUp()
    {
        $this->fileInfo = [
            'name'     => 'stream1.txt',
            'type'     => 'application/text',
            'tmp_name' => __DIR__ . '/stream1.txt',
            'size'     => filesize(__DIR__ . '/stream1.txt'),
        ];
        $this->uploaded = new UploadedFile($this->fileInfo);
    }

    public function testGetStream()
    {
        try {
            $stream = $this->uploaded->getStream();
            $this->assertInstanceOf('\\PsrEasy\\Http\\Message\\Stream', $stream);
        } catch (\RuntimeException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testMoveTo()
    {
        $this->setExpectedException('\RuntimeException');
        $this->uploaded->moveTo(__DIR__ . '/stream2.txt');
    }

    public function testGetSize()
    {
        $this->assertEquals($this->fileInfo['size'], $this->uploaded->getSize());
    }

    public function testGetError()
    {
        $this->assertEquals($this->fileInfo['error'], $this->uploaded->getError());
    }

    public function testClientFilename()
    {
        $this->assertEquals($this->fileInfo['name'], $this->uploaded->getClientFilename());
    }

    public function testGetClientMediaType()
    {
        $this->assertEquals($this->fileInfo['type'], $this->uploaded->getClientMediaType());
    }
}
