<?php
namespace Nmullen\ApiEngine\Test\Http;

use Nmullen\Http\Response;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Response
     */
    private $response;

    public function setUp()
    {
        $this->response = new Response();
    }

    public function testGetResponseCode()
    {
        $this->assertSame(200, $this->response->getStatusCode());
    }

    public function testGetResponseReasonPhrase()
    {
        $this->assertSame('OK', $this->response->getReasonPhrase());
    }

    public function testChangeResponseCode()
    {
        $new = $this->response->withStatus(404);
        $this->assertSame(404, $new->getStatusCode());
        $this->assertSame('Not Found', $new->getReasonPhrase());
        $this->assertNotSame($new, $this->response);
    }

    public function testInvalidStatusChange()
    {
        $this->setExpectedException('InvalidArgumentException');
        $new = $this->response->withStatus(1000);
        $this->assertSame(620, $new->getStatusCode());
    }
}
