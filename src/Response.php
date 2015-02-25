<?php

namespace Nmullen\Http;

use \InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamableInterface;

class Response implements ResponseInterface
{
    use Message;
    use StatusCode;

    private $body;

    private $statusCode;

    private $reasonPhrase;

    public function __construct($status = 200, array $headers = [], StreamableInterface $body = null)
    {
        $this->validateStatus('status', $status);
        $this->body = $body;
        $this->statusCode = $status;
        $this->headers = $this->parseHeaders($headers);
    }

    /**
     * Gets the response Status-Code.
     *
     * The Status-Code is a 3-digit integer result code of the server's attempt
     * to understand and satisfy the request.
     *
     * @return integer Status code.
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Create a new instance with the specified status code, and optionally
     * reason phrase, for the response.
     *
     * If no Reason-Phrase is specified, implementations MAY choose to default
     * to the RFC 7231 or IANA recommended reason phrase for the response's
     * Status-Code.
     *
     * This method MUST be implemented in such a way as to retain the
     * immutability of the message, and MUST return a new instance that has the
     * updated status and reason phrase.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @param integer $code The 3-digit integer result code to set.
     * @param null|string $reasonPhrase The reason phrase to use with the
     *     provided status code; if none is provided, implementations MAY
     *     use the defaults as suggested in the HTTP specification.
     * @return self
     * @throws \InvalidArgumentException For invalid status code arguments.
     */
    public function withStatus($code, $reasonPhrase = null)
    {
        $this->validateStatus('code', $code);
        $new = clone $this;
        $new->statusCode = $code;
        $new->reasonPhrase = $reasonPhrase;
        return $new;
    }

    /**
     * Gets the response Reason-Phrase, a short textual description of the Status-Code.
     *
     * Because a Reason-Phrase is not a required element in a response
     * Status-Line, the Reason-Phrase value MAY be null. Implementations MAY
     * choose to return the default RFC 7231 recommended reason phrase (or those
     * listed in the IANA HTTP Status Code Registry) for the response's
     * Status-Code.
     *
     * @link http://tools.ietf.org/html/rfc7231#section-6
     * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     * @return string|null Reason phrase, or null if unknown.
     */
    public function getReasonPhrase()
    {
        if (is_null($this->reasonPhrase)) {
            return $this->getReasonByCode($this->statusCode);
        }
    }

    private function validateStatus($var, $status)
    {
        if (!is_int($status)) {
            throw new InvalidArgumentException(sprintf('Expected Integer, given %s', gettype($status)));
        }
        if ($status < 0 || $status > 999) {
            throw new InvalidArgumentException(sprintf('%s is out of range 0-999', $status));
        }
    }
}