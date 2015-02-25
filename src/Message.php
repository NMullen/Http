<?php

namespace Nmullen\Http;

use Psr\Http\Message\StreamableInterface;

trait Message
{

    private $protocol = '1.1';

    private $headers = [];

    private $body;

    /**
     * Retrieves the HTTP protocol version as a string.
     *
     * The string MUST contain only the HTTP version number (e.g., "1.1", "1.0").
     *
     * @return string HTTP protocol version.
     */
    public function getProtocolVersion()
    {
        return $this->protocol;
    }

    /**
     * Create a new instance with the specified HTTP protocol version.
     *
     * The version string MUST contain only the HTTP version number (e.g.,
     * "1.1", "1.0").
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * new protocol version.
     *
     * @param string $version HTTP protocol version
     * @return self
     */
    public function withProtocolVersion($version)
    {
        $new = clone $this;
        $new->protocol = $version;
        return $new;
    }

    /**
     * Retrieves all message headers.
     *
     * The keys represent the headers name as it will be sent over the wire, and
     * each value is an array of strings associated with the headers.
     *
     *     // Represent the headers as a string
     *     foreach ($message->getHeaders() as $name => $values) {
     *         echo $name . ": " . implode(", ", $values);
     *     }
     *
     *     // Emit headers iteratively:
     *     foreach ($message->getHeaders() as $name => $values) {
     *         foreach ($values as $value) {
     *             headers(sprintf('%s: %s', $name, $value), false);
     *         }
     *     }
     *
     * While headers names are not case-sensitive, getHeaders() will preserve the
     * exact case in which headers were originally specified.
     *
     * @return array Returns an associative array of the message's headers. Each
     *     key MUST be a headers name, and each value MUST be an array of strings.
     */
    public function getHeaders()
    {
        return (is_array($this->headers) ? $this->headers : array());
    }

    /**
     * Retrieve a headers by the given case-insensitive name, as a string.
     *
     * This method returns all of the headers values of the given
     * case-insensitive headers name as a string concatenated together using
     * a comma.
     *
     * NOTE: Not all headers values may be appropriately represented using
     * comma concatenation. For such headers, use getHeaderLines() instead
     * and supply your own delimiter when concatenating.
     *
     * @param string $name Case-insensitive headers field name.
     * @return string
     */
    public function getHeader($name)
    {
        return (implode(', ', $this->getHeaderLines($name)));
        //return implode(',', $this->getHeaderLines($name));
    }

    /**
     * Checks if a headers exists by the given case-insensitive name.
     *
     * @param string $name Case-insensitive headers field name.
     * @return bool Returns true if any headers names match the given headers
     *     name using a case-insensitive string comparison. Returns false if
     *     no matching headers name is found in the message.
     */
    public function hasHeader($name)
    {
        return array_key_exists($this->findKeyCase($name), $this->headers);
    }

    /**
     * Retrieves a headers by the given case-insensitive name as an array of strings.
     *
     * @param string $name Case-insensitive headers field name.
     * @return string[]
     */
    public function getHeaderLines($name)
    {
        $name = $this->findKeyCase($name);
        $header = array_key_exists($name, $this->headers) ? $this->headers[$name] : [];
        return (is_array($header) ? $header : [$header]);
    }

    /**
     * Creates a new instance, with the specified headers appended with the
     * given value.
     *
     * Existing values for the specified headers will be maintained. The new
     * value(s) will be appended to the existing list. If the headers did not
     * exist previously, it will be added.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * new headers and/or value.
     *
     * @param string $name Case-insensitive headers field name to add.
     * @param string|string[] $value Header value(s).
     * @return self
     * @throws \InvalidArgumentException for invalid headers names or values.
     */
    public function withAddedHeader($name, $value)
    {
        $header = $this->parseHeaders([$name => $value]);

        $new = clone $this;
        $new->headers = array_merge($this->headers, $header);
        return $new;
    }

    /**
     * Create a new instance with the provided headers, replacing any existing
     * values of any headers with the same case-insensitive name.
     *
     * While headers names are case-insensitive, the casing of the headers will
     * be preserved by this function, and returned from getHeaders().
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * new and/or updated headers and value.
     *
     * @param string $name Case-insensitive headers field name.
     * @param string|string[] $value Header value(s).
     * @return self
     * @throws \InvalidArgumentException for invalid headers names or values.
     */
    public function withHeader($name, $value)
    {
        $header = $this->parseHeaders([$name => $value]);
        $headers = $this->headers;
        $headers = $headers[$name] = null;

        $new = clone $this;
        $new->headers = array_merge($this->headers, $header);
        return $new;
    }

    /**
     * Creates a new instance, without the specified headers.
     *
     * Header resolution MUST be done without case-sensitivity.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that removes
     * the named headers.
     *
     * @param string $name Case-insensitive headers field name to remove.
     * @return self
     */
    public function withoutHeader($name)
    {
        if (!$this->hasHeader($name)) {
            return $this;
        }

        $new = clone $this;
        unset($new->headers[$name]);
        return $new;
    }

    /**
     * Gets the body of the message.
     *
     * @return StreamableInterface Returns the body as a stream.
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Create a new instance, with the specified message body.
     *
     * The body MUST be a StreamableInterface object.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * new body stream.
     *
     * @param StreamableInterface $body Body.
     * @return self
     * @throws \InvalidArgumentException When the body is not valid.
     */
    public function withBody(StreamableInterface $body)
    {
        $new = clone $this;
        $new->body = $body;
        return $new;
    }

    /**
     * Case-insensitive array key lookup.
     * returns an existing key in its case if it is already set
     * otherwise returns the
     * @param $Key string search key
     * @return string
     */
    private function findKeyCase($Key)
    {
        $sKey = strtolower($Key);
        $index = [];
        foreach($this->headers as $key => $value){
            $index[strtolower($key)] = $key;
        }
        if(array_key_exists($sKey, $index)){
            return $index[$sKey];
        }
        return $Key;
    }

    /**
     * @param array $headers
     * @return array headers formatted as [key] = [value]
     */
    private function parseHeaders(array $headers = [])
    {
        $result = [];
        foreach($headers as $key => $value)
        {
            $key = $this->findKeyCase($key);
            if(!is_array($value)) {
                $value = explode(', ', $value);
            }
            $result = array_merge($result, [$key => $value]);
        }
        return $result;
    }
}