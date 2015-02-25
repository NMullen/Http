<?php

namespace Nmullen\Http;

use Nmullen\ApiEngine\Exception\InvalidArgumentException;
use Psr\Http\Message\StreamableInterface;

class Stream implements StreamableInterface
{

    private $resource;

    public function __construct($stream, $mode = 'rw+')
    {
        $this->attach($stream, $mode);
    }

    public function attach($resource, $mode)
    {
        if (is_resource($resource)) {
            $this->resource = $resource;
        } elseif (is_string($resource)) {
            $this->resource = fopen($resource, $mode);
        }
        if (!is_resource($this->resource)) {
            throw InvalidArgumentException::InvalidStreamProvided($resource);
        }
    }

    /**
     * Closes the stream and any underlying resources.
     *
     * @return void
     */
    public function close()
    {
        $resource = $this->detach();
        fclose($resource);
    }

    /**
     * Separates any underlying resources from the stream.
     *
     * After the stream has been detached, the stream is in an unusable state.
     *
     * @return resource|null Underlying PHP stream, if any
     */
    public function detach()
    {
        $resource = $this->resource;
        $this->resource = null;
        return $resource;
    }

    /**
     * Get the size of the stream if known
     *
     * @return int|null Returns the size in bytes if known, or null if unknown.
     */
    public function getSize()
    {
        return null;
    }

    /**
     * Returns the current position of the file read/write pointer
     *
     * @return int|bool Position of the file pointer or false on error.
     */
    public function tell()
    {
        return ($this->resource ? ftell($this->resource) : false);
    }

    /**
     * Seek to a position in the stream.
     *
     * @link http://www.php.net/manual/en/function.fseek.php
     * @param int $offset Stream offset
     * @param int $whence Specifies how the cursor position will be calculated
     *     based on the seek offset. Valid values are identical to the built-in
     *     PHP $whence values for `fseek()`.  SEEK_SET: Set position equal to
     *     offset bytes SEEK_CUR: Set position to current location plus offset
     *     SEEK_END: Set position to end-of-stream plus offset.
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        if (!$this->isSeekable()) {
            return false;
        }
        $result = fseek($this->resource, $offset, $whence);
        return (0 === $result);
    }

    /**
     * Returns whether or not the stream is seekable.
     *
     * @return bool
     */
    public function isSeekable()
    {
        return ($this->resource ? $this->getMetadata('seekable') : false);
    }

    /**
     * Get stream metadata as an associative array or retrieve a specific key.
     *
     * The keys returned are identical to the keys returned from PHP's
     * stream_get_meta_data() function.
     *
     * @link http://php.net/manual/en/function.stream-get-meta-data.php
     * @param string $key Specific metadata to retrieve.
     * @return array|mixed|null Returns an associative array if no key is
     *     provided. Returns a specific key value if a key is provided and the
     *     value is found, or null if the key is not found.
     */
    public function getMetadata($key = null)
    {
        if (null === $key) {
            return stream_get_meta_data($this->resource);
        }
        $metadata = stream_get_meta_data($this->resource);
        if (!array_key_exists($key, $metadata)) {
            return null;
        }
        return $metadata[$key];
    }

    /**
     * Returns whether or not the stream is writable.
     *
     * @return bool
     */
    public function isWritable()
    {
        if (!$this->resource) {
            return false;
        }
        if (!$this->getMetadata('uri')) {
            return false;
        }
        $mode = $this->getMetadata('mode');
        return (strstr($mode, 'w') || strstr($mode, '+'));
    }

    /**
     * Write data to the stream.
     *
     * @param string $string The string that is to be written.
     * @return int|bool Returns the number of bytes written to the stream on
     *     success or FALSE on failure.
     */
    public function write($string)
    {
        if (!$this->resource) {
            return false;
        }
        $result = fwrite($this->resource, $string);
        return (0 === $result ? false : $result);
    }

    /**
     * Read data from the stream.
     *
     * @param int $length Read up to $length bytes from the object and return
     *     them. Fewer than $length bytes may be returned if underlying stream
     *     call returns fewer bytes.
     * @return string|false Returns the data read from the stream, false if
     *     unable to read or if an error occurs.
     */
    public function read($length)
    {
        if (!$this->resource || !$this->isReadable()) {
            return false;
        }
        if ($this->eof()) {
            return '';
        }
        return fread($this->resource, $length);
    }

    /**
     * Returns whether or not the stream is readable.
     *
     * @return bool
     */
    public function isReadable()
    {
        if (!$this->resource) {
            return false;
        }
        $mode = $this->getMetadata('mode');
        return (strstr($mode, 'r') || strstr($mode, '+'));
    }

    /**
     * Returns true if the stream is at the end of the stream.
     *
     * @return bool
     */
    public function eof()
    {
        return ($this->resource ? feof($this->resource) : true);
    }

    function __toString()
    {
        return $this->getContents();
    }

    /**
     * Returns the remaining contents in a string
     *
     * @return string
     */
    public function getContents()
    {
        if (!$this->isReadable()) {
            return false;
        }
        $this->rewind();
        return stream_get_contents($this->resource);
    }

    /**
     * Seek to the beginning of the stream.
     *
     * If the stream is not seekable, this method will return FALSE, indicating
     * failure; otherwise, it will perform a seek(0), and return the status of
     * that operation.
     *
     * @see seek()
     * @link http://www.php.net/manual/en/function.fseek.php
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function rewind()
    {
        if (!$this->isSeekable()) {
            return false;
        }
        $result = fseek($this->resource, 0);
        return (0 === $result);
    }
}