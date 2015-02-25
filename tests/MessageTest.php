<?php
namespace Nmullen\Http\Test;

class MessageTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var MessageImp
     */
    private $message;

    public function setUp()
    {
        $this->message = new MessageImp();
    }

    public function testProtocolReturnsDefault()
    {
        $this->assertNotNull($this->message->getProtocolVersion());
    }

    public function testWithProtocolReturnsClone()
    {
        $protocol = 1.1;
        $this->assertNotSame($protocol, $this->message->getProtocolVersion());
        $new = $this->message->withProtocolVersion(1.1);
        $this->assertNotSame($this->message, $new);
        $this->assertEquals($new->getProtocolVersion(), 1.1);
    }

    public function testHeadersReturnsEmpty()
    {
        $this->assertTrue(is_array($this->message->getHeaders()));
        $this->assertCount(0, $this->message->getHeaders());
    }

    public function testSetHeaderReturnsClone()
    {
        $new = $this->message->withHeader('key', 'value');
        $this->assertNotSame($new, $this->message);
        $this->assertSame('value', $new->getHeader('Key'));
    }

    public function testWithHeader()
    {
        $new = $this->message->withHeader('Test', 'value');
        $this->assertSame('value', $new->getHeader('TEST'));
    }

    public function testParseHeader()
    {
        $header = [
            'Transfer-Encoding' => 'chunked',
            'Date' => '24 March 1989',
            'Vary' => 'Accept-Encoding, Cookie, User-Agent',
        ];
        $this->message->parseHeadersForTest($header);
        foreach ($header as $key => $value) {
            $this->assertSame($value, $this->message->getHeader($key));
        }
    }

    public function testPreserveCase()
    {
        $header = [
            'Transfer-Encoding' => 'chunked',
            'Date' => '24 March 1989',
            'Vary' => 'Accept-Encoding, Cookie, User-Agent',
        ];
        $this->message->parseHeadersForTest($header);
        $headerCopy = $this->message->getHeaders();
        foreach ($header as $key => $value) {
            $this->assertArrayHasKey($key, $headerCopy);
        }
    }
}