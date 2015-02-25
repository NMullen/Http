<?php
namespace Nmullen\Http\Test;

use Nmullen\Http\Request;
use Nmullen\Http\Uri;

class RequestTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Request
     */
    private $request;

    public function setUp()
    {
        $this->request = new Request();
    }

    public function testRequestTargetMustReturnBackSlash()
    {
        $this->assertSame('/', $this->request->getRequestTarget());
    }

    public function testWithRequestTargetReturnsClone()
    {
        $new = $this->request->withRequestTarget('target');
        $this->assertNotSame($new, $this->request);
        $this->assertSame('target', $new->getRequestTarget());
    }

    public function testWithRequestTargetIsSlashWithNoPathOrQuery(){
        $new = $this->request->withUri(new Uri('http://www.example.com'));
        $this->assertSame('/', $new->getRequestTarget());
    }

    /**
     * @dataProvider requestTargets
     */
    public function testReturnsRequestTargetWithUri($request, $expected){
        $new = new Request(null, new Uri($request));
        $this->assertSame($expected, $new->getRequestTarget());
    }

    public function testMethodReturnsNullByDefault()
    {
        $this->assertNull($this->request->getMethod());
    }

    /**
     * @dataProvider allowedMethodsAreCaseInsensitive
     */
    public function testMethodWithHttpMethods($method)
    {
        $request = $this->request->withMethod($method);
        $this->assertSame($method, $request->getMethod());
    }

    /**
     * @dataProvider notAllowedMethods
     */
    public function testMethodWithInvalidMethods($method)
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->request->withMethod($method);
    }

    public function testWithMethodReturnsClone(){
        $new = $this->request->withMethod('GET');
        $this->assertNotSame($new, $this->request);
    }

    /**
     * @dataProvider allowedMethodsAreCaseInsensitive
     */
    public function testConstructorWithValidMethods($method)
    {
        $new = new Request($method);
        $this->assertSame($method, $new->getMethod());
    }

    /**
     * @dataProvider notAllowedMethods
     */
    public function testConstructorWithInvalidMethods($method)
    {
        $this->setExpectedException('InvalidArgumentException');
        $new = new Request($method);
    }

    public function testConstructorWithUri()
    {
        $uri =  new Uri('http://www.example.com/test?foo=bar');
        $request = new Request(null, $uri);
        $this->assertSame($uri, $request->getUri());
        $this->assertSame('/test?foo=bar', $request->getRequestTarget());
    }

    public function testWithUriResetsRequestTarget()
    {
        $original = $this->request->withRequestTarget('*');
        $this->assertSame('*', $original->getRequestTarget());
        $new = $original->withUri(new Uri('http://www.example.com/test'));
        $this->assertNotSame('*', $new->getRequestTarget());
    }

    public function requestTargets(){
        return [
            ['http://www.example.com/test', '/test'],
            ['http://www.example.com/test?foo=bar', '/test?foo=bar'],
            ['/test', '/test'],
            ['/test?foo=bar', '/test?foo=bar'],
            ['/test?foo=bar#help', '/test?foo=bar']
        ];
    }
    public function allowedMethodsAreCaseInsensitive()
    {
        return [
            ['connect'],
            ['DELETE'],
            ['GET'],
            ['head'],
            ['OPTIONS'],
            ['PATCH'],
            ['POST'],
            ['put'],
            ['TRACE'],
        ];
    }

    public function notAllowedMethods()
    {
        return [
            ['int' => 1],
            ['string' => 'notGet'],
            ['array' => ['test']],
            ['object' => new \stdClass()]
        ];
    }
}
