<?php
namespace PsrEasy\Tests\Http\Message;

use PsrEasy\Http\Message\Uri;

class UriTest extends \PHPUnit_Framework_TestCase
{
    private $uri1 = null;

    private $uri2 = null;

    private $uri3 = null;

    private $uri4 = null;

    private $uri5 = null;

    public function setUp()
    {
        $this->uri1 = new Uri('http://user1:pass1@test.abc.com:8080/aaa/bbb.php?a=ss&b=tt#f123456');
        $this->uri2 = new Uri('http://test.abc.com/最新/aaa/bbb.php?a=日期&b=tt#f123456测试');
        $this->uri3 = new Uri('https://test.abc.com');
        $this->uri4 = new Uri('test.abc.com:80/aaa+bbb/bbb.php?a=ss&b=tt#f123456');
        $this->uri5 = new Uri('/aaa/bbb.php?a=ss&b=tt#f123456');
    }

    public function testGetScheme()
    {
        $this->assertEquals('http', $this->uri1->getScheme());
        $this->assertEquals('http', $this->uri2->getScheme());
        $this->assertEquals('https', $this->uri3->getScheme());
        $this->assertEquals('', $this->uri4->getScheme());
        $this->assertEquals('', $this->uri5->getScheme());
    }

    public function testGetAuthority()
    {
        $this->assertEquals('user1:pass1@test.abc.com:8080', $this->uri1->getAuthority());
        $this->assertEquals('test.abc.com', $this->uri2->getAuthority());
        $this->assertEquals('test.abc.com', $this->uri3->getAuthority());
        $this->assertEquals('test.abc.com:80', $this->uri4->getAuthority());
        $this->assertEquals('', $this->uri5->getAuthority());
    }

    public function testGetUserInfo()
    {
        $this->assertEquals('user1:pass1', $this->uri1->getUserInfo());
        $this->assertEquals('', $this->uri2->getUserInfo());
        $this->assertEquals('', $this->uri3->getUserInfo());
        $this->assertEquals('', $this->uri4->getUserInfo());
        $this->assertEquals('', $this->uri5->getUserInfo());
    }

    public function testGetHost()
    {
        $this->assertEquals('test.abc.com', $this->uri1->getHost());
        $this->assertEquals('test.abc.com', $this->uri2->getHost());
        $this->assertEquals('test.abc.com', $this->uri3->getHost());
        $this->assertEquals('test.abc.com', $this->uri4->getHost());
        $this->assertEquals('', $this->uri5->getHost());
    }

    public function testGetPort()
    {
        $this->assertEquals(8080, $this->uri1->getPort());
        $this->assertEquals(null, $this->uri2->getPort());
        $this->assertEquals(null, $this->uri3->getPort());
        $this->assertEquals(80, $this->uri4->getPort());
        $this->assertEquals(null, $this->uri5->getPort());
    }

    public function testGetPath()
    {
        $this->assertEquals('/aaa/bbb.php', $this->uri1->getPath());
        $this->assertEquals('/%E6%9C%80%E6%96%B0/aaa/bbb.php', $this->uri2->getPath());
        $this->assertEquals('', $this->uri3->getPath());
        $this->assertEquals('/aaa%2Bbbb/bbb.php', $this->uri4->getPath());
        $this->assertEquals('/aaa/bbb.php', $this->uri5->getPath());
    }

    public function testGetQuery()
    {
        $this->assertEquals('a=ss&b=tt', $this->uri1->getQuery());
        $this->assertEquals('a=%E6%97%A5%E6%9C%9F&b=tt', $this->uri2->getQuery());
        $this->assertEquals('', $this->uri3->getQuery());
        $this->assertEquals('a=ss&b=tt', $this->uri4->getQuery());
        $this->assertEquals('a=ss&b=tt', $this->uri5->getQuery());
    }

    public function testGetFragment()
    {
        $this->assertEquals('f123456', $this->uri1->getFragment());
        $this->assertEquals('f123456%E6%B5%8B%E8%AF%95', $this->uri2->getFragment());
        $this->assertEquals('', $this->uri3->getFragment());
        $this->assertEquals('f123456', $this->uri4->getFragment());
        $this->assertEquals('f123456', $this->uri5->getFragment());
    }

    public function testWithScheme()
    {
        $scheme = 'https';
        $this->assertEquals($this->uri1, $this->uri1->withScheme($scheme));
        $this->assertEquals($scheme, $this->uri1->getScheme());
    }

    public function testWithUserInfo()
    {
        $user = 'user2';
        $pass = 'pass2';
        $this->assertEquals($this->uri2, $this->uri2->withUserInfo($user, $pass));
        $this->assertEquals("$user:$pass", $this->uri2->getUserInfo());
    }

    public function testWithHost()
    {
        $host = 'test1.abc.com';
        $this->assertEquals($this->uri1, $this->uri1->withHost($host));
        $this->assertEquals($host, $this->uri1->getHost());

        $host = '123';

        try {
            $this->uri1->withHost($host);
        } catch (\InvalidArgumentException $e) {
            return;
        }

        $this->fail('No expected exception for withHost');
    }

    public function testWithPort()
    {
        $port = 8081;
        $this->assertEquals($this->uri1, $this->uri1->withPort($port));
        $this->assertEquals($port, $this->uri1->getPort());

        $port = 65536;

        try {
            $this->uri1->withPort($port);
        } catch (\InvalidArgumentException $e) {
            return;
        }

        $this->fail('No expected exception for withPort');
    }

    public function testWithPath()
    {
        $path = '/aaa/path2/bbb.php';
        $this->assertEquals($this->uri1, $this->uri1->withPath($path));
        $this->assertEquals($path, $this->uri1->getPath());
    }

    public function testWithQuery()
    {
        $query = 'a=ss&b=tt&c=gg&d=%E6%B1%BD%E8%BD%A6';
        $this->assertEquals($this->uri1, $this->uri1->withQuery($query));
        $this->assertEquals($query, $this->uri1->getQuery());

        $query = 'a=ss&b=tt&c=gg&d=汽车';
        $this->assertEquals($this->uri1, $this->uri1->withQuery($query));
        $this->assertEquals('a=ss&b=tt&c=gg&d=%E6%B1%BD%E8%BD%A6', $this->uri1->getQuery());

        $query = '?a=ss&b=tt&c=gg&d=汽车';

        try {
            $this->uri1->withQuery($query);
        } catch (\InvalidArgumentException $e) {
            return;
        }

        $this->fail('No expected exception for withHost');
    }

    public function testWithFragment()
    {
        $fragment = 'f123456789';
        $this->assertEquals($this->uri1, $this->uri1->withFragment($fragment));
        $this->assertEquals($fragment, $this->uri1->getFragment());

        $fragment = 'f123456789测试1';
        $this->assertEquals($this->uri1, $this->uri1->withFragment($fragment));
        $this->assertEquals('f123456789%E6%B5%8B%E8%AF%951', $this->uri1->getFragment());

        $fragment = '#f123456789';

        try {
            $this->uri1->withFragment($fragment);
        } catch (\InvalidArgumentException $e) {
            return;
        }

        $this->fail('No expected exception for withHost');
    }

    public function testToString()
    {
        $uri = 'http://user1:pass1@test.abc.com:8080/aaa/bbb.php?a=ss&b=tt#f123456';
        $this->assertEquals($uri, (string) $this->uri1);
    }
}
