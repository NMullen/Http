<?php
namespace Nmullen\ApiEngine\Test\Http;

use Nmullen\Http\Stream;

class StreamTest extends \PHPUnit_Framework_TestCase
{

    const READONLY = 'r';
    const READ_WRITE = 'rb+';
    const WRITEONLY = 'w';

    private $tmp;
    /**
     * @var Stream
     */
    private $stream;

    public function setUp()
    {
        $this->tmp = tempnam(sys_get_temp_dir(), 'stream');
        $this->stream = new Stream($this->tmp);
    }

    public function testConstructionWithPath()
    {
        $this->assertInstanceOf('\\Nmullen\\Http\\Stream', $this->stream);
    }

    public function testConstructionWithResource()
    {
        $resource = fopen('php://memory', 'wb+');
        $stream = new Stream($resource);
        $this->assertInstanceOf('\\Nmullen\\Http\\Stream', $stream);
    }

    public function testNoResource()
    {
        $this->stream->close();
        $this->assertNull($this->stream->getSize());
        $this->assertFalse(false);
    }

    public function testCanReadReadOnlyMode()
    {
        $stream = new Stream($this->tmp, self::READONLY);
        $this->assertTrue($stream->isReadable());
    }

    public function testCanNotWriteReadOnlyMode()
    {
        $stream = new Stream($this->tmp, self::READONLY);
        $this->assertFalse($stream->isWritable());
        $this->assertFalse($stream->write('Then it hits me'));
    }

    public function testCanReadWriteReadWriteMode()
    {
        $stream = new Stream($this->tmp, self::READ_WRITE);
        $this->assertTrue($stream->isWritable());
        $this->assertTrue($stream->isReadable());
    }

    public function testWriteModeIsWriteable()
    {
        $stream = new Stream($this->tmp, self::WRITEONLY);
        $this->assertTrue($stream->isWritable());
    }

    public function testWriteModeCanWrite()
    {
        $stream = new Stream($this->tmp, self::WRITEONLY);
        $this->assertSame(37, $stream->write('Sunshine on my window, Makes me happy'));
    }

    public function testWriteOnlyModeCanNotRead()
    {
        $stream = new Stream($this->tmp, self::WRITEONLY);
        $this->assertSame(37, $stream->write('Sunshine on my window, Makes me happy'));
        $this->assertFalse($stream->read(0));
    }

    public function testClosedCanNotReadOrWrite()
    {
        $stream = new Stream($this->tmp, self::READ_WRITE);
        $this->assertTrue($stream->isReadable());
        $this->assertTrue($stream->isWritable());
        $stream->close();
        $this->assertFalse($stream->isReadable());
        $this->assertFalse($stream->isWritable());
    }

    public function testCanNotWriteOnCLose()
    {
        $stream = new Stream($this->tmp, self::READ_WRITE);
        $this->assertSame(17, $stream->write('Like I should be,'));
        $stream->close();
        $this->assertFalse($stream->write('Outside, all around me, really sleazy'));
    }

    public function testReadReturnsFalseOnClose()
    {
        $stream = new Stream($this->tmp);
        $stream->close();
        $this->assertFalse($stream->read(20));
    }

    public function testSizeReturnsNull()
    {
        $this->stream->write('Meanwhile In the moonlight Purple people Unforseeable');
        $this->assertNull($this->stream->getSize());
    }

    public function testSeekIsSeekable()
    {
        $resource = fopen($this->tmp, self::READ_WRITE);
        fwrite($resource, 'Don\'t tell me, You can\'t see, What it means to me, Me me me');
        $stream = new Stream($resource);
        $stream->seek(25);
        $this->assertSame('see', $stream->read(3));
    }

    public function testTellReturnsPosition()
    {
        $stream = new Stream($this->tmp, self::READ_WRITE);
        $this->assertSame(0, $stream->tell());
        $stream->write('Lonely, As they may be, They\'ll be peachy, Then it hits me');
        $this->assertSame(58, $stream->tell());
    }

    public function testRewindDoesRewind()
    {
        $stream = new Stream($this->tmp, self::READ_WRITE);
        $stream->write('Lonely, As they may be, They\'ll be peachy, Then it hits me');
        $this->assertSame(58, $stream->tell());
        $this->assertTrue($stream->rewind());
        $this->assertEquals(0, $stream->tell());
    }
}
