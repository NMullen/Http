<?php
namespace Nmullen\Http\Test;

use Nmullen\Http\Message;

class MessageImp
{
    use Message;

    public function parseHeadersForTest($headers)
    {
        $this->headers = $this->parseHeaders($headers);
    }
}