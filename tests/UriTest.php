<?php
namespace Nmullen\ApiEngine\Test\Http;

use Nmullen\Http\Uri;

class UriTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Uri
     */
    private $uri;

    public function setUp()
    {
        $this->uri = new Uri();
    }

    public function testConstructionSetsAllValues()
    {
        $uri = new Uri('http://user:pass@hostname:80/path?query=value#fragment_a');
        $this->assertSame('http', $uri->getScheme());
        $this->assertSame('user:pass@hostname', $uri->getAuthority());
        $this->assertSame('hostname', $uri->getHost());
        $this->assertnull($uri->getPort());
        $this->assertSame('/path', $uri->getPath());
        $this->assertSame('query=value', $uri->getQuery());
        $this->assertSame('fragment_a', $uri->getFragment());
    }

    public function testNoSchemeReturnsEmptyString()
    {
        $uri = new Uri('hostname/path');
        $this->assertSame('', $uri->getScheme());
    }

    public function testNonStandardSchemeReturnsEmptyString()
    {
        $uri = new Uri('udp://hostname/');
        $this->assertSame('', $uri->getScheme());
    }

    public function testNoAuthorityReturnsEmptyString()
    {
        $this->assertSame('', $this->uri->getAuthority());
    }

    public function testUserAtHostReturnsWithoutColon()
    {
        $uri = new Uri('http://user@hostname');
        $this->assertSame('user@hostname', $uri->getAuthority());
    }

    public function testNoHostReturnsEmptyString()
    {
        $uri = new Uri('/test/without/Host?returns=empty');
        $this->assertSame('', $uri->getHost());
    }

    public function testNonStandardPort()
    {
        $uri = new Uri('http://hostname:100/');
        $this->assertSame(100, $uri->getPort());
    }

    public function testPathReturnsEmptyString()
    {
        $this->assertSame('', $this->uri->getPath());
    }

    public function testQueryReturnsEmptyString()
    {
        $this->assertSame('', $this->uri->getQuery());
    }

    public function testFragmentReturnsEmptyString()
    {
        $this->assertSame('', $this->uri->getFragment());
    }

    public function testWithSchemeAllowsNewWithNull()
    {
        $https = $this->uri->withScheme('https');
        $this->assertSame('https', $https->getScheme());
        $empty = $https->withScheme(null);
        $this->assertSame('', $empty->getScheme());
    }

    public function testWithUserInfoReturnsNew()
    {
        $new = $this->uri->withUserInfo('test', 'password');
        $this->assertSame('test:password', $new->getUserInfo());
        $this->assertNotSame($new, $this->uri);
    }

    public function testWithUserInfoNoPasswordReturnsUsernameOnly()
    {
        $new = $this->uri->withUserInfo('test');
        $this->assertSame('test', $new->getUserInfo());
    }

    public function testWithNoUsernameReturnsEmpty()
    {
        $new = $this->uri->withUserInfo(null, 'test');
        $this->assertSame('', $new->getUserInfo());
    }

    public function testWithHostReturnsNew()
    {
        $new = $this->uri->withHost('newhostname');
        $this->assertNotSame($new, $this->uri);
        $this->assertSame('newhostname', $new->getHost());
    }

    public function testWithPortNotIntegerNotNull()
    {
        $this->setExpectedException('InvalidArgumentException');
        $new = $this->uri->withPort('a dirty string');
    }

    public function testWithPortLowOutOfRangeInt()
    {
        $this->setExpectedException('InvalidArgumentException');
        $new = $this->uri->withPort(-1);
    }

    public function testWithPortHighOutOfRangeInt()
    {
        $this->setExpectedException('InvalidArgumentException');
        $new = $this->uri->withPort(61001);
    }

    public function testWithPortWithNullOnNull()
    {
        $new = $this->uri->withPort(null);
        $this->assertSame($new, $this->uri);
    }

    public function testWithPortWithNullOnNonNull()
    {
        $new = $this->uri->withPort(1);
        $this->assertNotSame($new, $this->uri);
        $nullPort = $new->withPort(null);
        $this->assertNotSame($nullPort, $this->uri);
        $this->assertNull($nullPort->getPort());
    }

    public function testWithPathWithNull()
    {
        $this->setExpectedException('InvalidArgumentException');
        $new = $this->uri->withPath(null);
    }

    public function testWithPathWithNoPrefix()
    {
        $new = $this->uri->withPath('THEPATH');
        $this->assertSame('/THEPATH', $new->getPath());
    }

    public function testWithQueryNotString()
    {
        $this->setExpectedException('InvalidArgumentException');
        $new = $this->uri->withQuery(null);
    }

    public function testWithQueryWithValidString()
    {
        $new = $this->uri->withQuery('?this=that');
        $this->assertSame('this=that', $new->getQuery());
    }

    public function testWithFragmentNotString()
    {
        $this->setExpectedException('InvalidArgumentException');
        $new = $this->uri->withFragment(null);
    }

    public function testWithFragmentWithValidString()
    {
        $new = $this->uri->withFragment('#hashtaged');
        $this->assertSame('hashtaged', $new->getFragment());
    }

    /**
     * @dataProvider listOfParts()
     */
    public function testToString($string)
    {
        $uri = new Uri($string);
        $this->assertSame($string, (string)$uri);
    }

    public function listOfParts()
    {
        return [
            ['http://user:pass@hostname:100/path?query=value#fragment_a'],
            ['user@hostname/path?query=value#fragment_a'],
            ['hostname/path?query=value#fragment_a'],
            ['/path?query=value#fragment_a'],
            ['/path?query=value'],
            ['/path'],
            ['/'],
        ];
    }
}
