<?php
namespace Nmullen\Http;

use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamableInterface;
use Psr\Http\Message\UriInterface;

class Request implements RequestInterface
{
    use Message;

    private $method;

    private $requestTarget;

    private $uri;

    private $validMethods = [
        'CONNECT',
        'DELETE',
        'GET',
        'HEAD',
        'OPTIONS',
        'PATCH',
        'POST',
        'PUT',
        'TRACE',
    ];

    /**
     * @param null|string $method
     * @param null|UriInterface $uri
     * @param array $headers
     * @param StreamableInterface $body
     */
    public function __construct($method = null, $uri = null, array $headers = [], StreamableInterface $body = null)
    {
        if (!is_null($method)) {
            $this->validateMethod($method);
        }

        if (is_null($uri)) {
            $uri = new Uri();
        }

        if (is_null($body)) {
            $body = new Stream('php://memory');
        }

        $this->method = $method;
        $this->uri = $uri;
        $this->headers = $headers;
        $this->body = $body;
    }

    /**
     * Retrieves the message's request target.
     *
     * Retrieves the message's request-target either as it will appear (for
     * clients), as it appeared at request (for servers), or as it was
     * specified for the instance (see withRequestTarget()).
     *
     * In most cases, this will be the origin-form of the composed URI,
     * unless a value was provided to the concrete implementation (see
     * withRequestTarget() below).
     *
     * If no URI is available, and no request-target has been specifically
     * provided, this method MUST return the string "/".
     *
     * @return string
     */
    public function getRequestTarget()
    {
        if (!is_null($this->requestTarget)) {
            return $this->requestTarget;
        }

        if ($this->uri->getPath() === '') {
            return '/';
        }

        return ($this->uri->getQuery()
            ? $this->uri->getPath() . '?' . $this->uri->getQuery()
            : $this->uri->getPath()
        );
    }

    /**
     * Create a new instance with a specific request-target.
     *
     * If the request needs a non-origin-form request-target — e.g., for
     * specifying an absolute-form, authority-form, or asterisk-form —
     * this method may be used to create an instance with the specified
     * request-target, verbatim.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * changed request target.
     *
     * @link http://tools.ietf.org/html/rfc7230#section-2.7 (for the various
     *     request-target forms allowed in request messages)
     * @param mixed $requestTarget
     * @return self
     */
    public function withRequestTarget($requestTarget)
    {
        $new = clone $this;
        $new->requestTarget = $requestTarget;
        return $new;
    }

    /**
     * Retrieves the HTTP method of the request.
     *
     * @return string Returns the request method.
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Create a new instance with the provided HTTP method.
     *
     * While HTTP method names are typically all uppercase characters, HTTP
     * method names are case-sensitive and thus implementations SHOULD NOT
     * modify the given string.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * changed request method.
     *
     * @param string $method Case-insensitive method.
     * @return self
     * @throws \InvalidArgumentException for invalid HTTP methods.
     */
    public function withMethod($method)
    {
        $this->validateMethod($method);
        $new = clone $this;
        $new->method = $method;
        return $new;
    }

    /**
     * Retrieves the URI instance.
     *
     * This method MUST return a UriInterface instance.
     *
     * @link http://tools.ietf.org/html/rfc3986#section-4.3
     * @return UriInterface Returns a UriInterface instance
     *     representing the URI of the request, if any.
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Create a new instance with the provided URI.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * new UriInterface instance.
     *
     * @link http://tools.ietf.org/html/rfc3986#section-4.3
     * @param UriInterface $uri New request URI to use.
     * @return self
     */
    public function withUri(UriInterface $uri)
    {
        $new = clone $this;
        $new->requestTarget = null;
        $new->uri = $uri;
        return $new;
    }

    private function validateMethod($method)
    {
        if (is_string($method) && in_array(strtoupper($method), $this->validMethods)) {
            return true;
        }

        $message = (is_string($method))
            ? sprintf('%s is not a valid HTTP Method', $method)
            : sprintf('withMethod expects a string, given %s', gettype($method));
        throw new InvalidArgumentException($message);
    }
}