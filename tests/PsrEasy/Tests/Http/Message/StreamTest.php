<?php
namespace PsrEasy\Tests\Http\Message;

use PsrEasy\Http\Message\Stream;

class StreamTest extends \PHPUnit_Framework_TestCase
{
    private $file1 = '';

    private $file2 = '';

    private $string = '';

    private $stream1 = null;

    private $stream2 = null;

    private $stream3 = null;

    public function setUp()
    {
        $this->file1   = __DIR__ . '/stream1.txt';
        $this->file2   = __DIR__ . '/stream2.txt';
        $this->string  = 'this is a test';
        $this->stream1 = new Stream($this->file1, 'r');
        $this->stream2 = new Stream($this->file2, 'w');
        $this->stream3 = new Stream('php://memory', 'rw');
    }

    public function testToString()
    {
        $this->assertEquals(file_get_contents($this->file1), (string) $this->stream1);
        $this->assertEquals('', (string) $this->stream3);
        $this->stream3->write($this->string);
        $this->stream3->rewind();
        $this->assertEquals($this->string, (string) $this->stream3);
    }

    public function testClose()
    {
        $this->stream1->close();
        $this->stream2->close();
        $this->stream3->close();
    }

    public function testDetach()
    {
        $this->assertNotEquals(null, $this->stream1->detach());
        $this->assertEquals(null, $this->stream1->detach());
    }

    public function testGetSize()
    {
        $this->assertEquals(filesize($this->file1), $this->stream1->getSize());
        $this->stream3->write($this->string);
        $this->stream3->rewind();
        $this->assertEquals(strlen($this->string), $this->stream3->getSize());
    }

    public function testTell()
    {
        try {
            $this->assertGreaterThanOrEqual(0, $this->stream1->tell());
            $this->assertGreaterThanOrEqual(0, $this->stream2->tell());
            $this->assertGreaterThanOrEqual(0, $this->stream3->tell());
        } catch (\RuntimeException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testEof()
    {
        $this->assertEquals(false, $this->stream1->eof());
        $this->assertEquals(false, $this->stream2->eof());
        $this->assertEquals(false, $this->stream3->eof());
    }

    public function testIsSeekable()
    {
        $this->assertEquals(true, $this->stream1->isSeekable());
        $this->assertEquals(true, $this->stream2->isSeekable());
        $this->assertEquals(true, $this->stream3->isSeekable());
    }

    public function testSeek()
    {
        try {
            $this->stream1->seek(2);
            $this->stream1->seek(2, SEEK_SET);
            $this->stream1->seek(1, SEEK_CUR);
            $this->stream1->seek(-1, SEEK_END);

            $this->stream2->seek(2);
            $this->stream2->seek(2, SEEK_SET);
            $this->stream2->seek(1, SEEK_CUR);
            $this->stream2->seek(0, SEEK_END);

            $this->stream3->seek(0);
            $this->stream3->seek(0, SEEK_SET);
            $this->stream3->seek(0, SEEK_CUR);
            $this->stream3->seek(0, SEEK_END);
        } catch (\RuntimeException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testRewind()
    {
        try {
            $this->stream1->rewind();
            $this->stream2->rewind();
            $this->stream3->rewind();
        } catch (\RuntimeException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testIsWritable()
    {
        $this->assertEquals(false, $this->stream1->isWritable());
        $this->assertEquals(true, $this->stream2->isWritable());
        $this->assertEquals(true, $this->stream3->isWritable());
    }

    public function testWrite()
    {
        $this->assertEquals(3, $this->stream2->write('abc'));
        $this->assertEquals(3, $this->stream3->write('abc'));
        $this->assertEquals('abc', (string) $this->stream3);
    }

    public function testIsReadable()
    {
        $this->assertEquals(true, $this->stream1->isReadable());
        $this->assertEquals(false, $this->stream2->isReadable());
        $this->assertEquals(true, $this->stream3->isReadable());
    }

    public function testRead()
    {
        try {
            $length = 5;
            $this->assertEquals($length, strlen($this->stream1->read($length)));
            $this->stream3->write($this->string);
            $this->stream3->rewind();
            $this->assertEquals($length, strlen($this->stream3->read($length)));
        } catch (\RuntimeException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testGetContents()
    {
        try {
            $length = 5;
            $total  = strlen(file_get_contents($this->file1));
            $this->assertEquals($length, strlen($this->stream1->read($length)));
            $this->assertEquals($total - $length, strlen($this->stream1->getContents()));
        } catch (\RuntimeException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testGetMetaData()
    {
        $this->assertEquals(null, $this->stream1->getMetaData('abc'));
        $this->assertEquals(true, $this->stream1->getMetaData('seekable'));
        $this->assertArrayHasKey('mode', $this->stream1->getMetaData());
    }
}
