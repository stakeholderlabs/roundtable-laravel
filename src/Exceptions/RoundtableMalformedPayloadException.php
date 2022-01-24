<?php

namespace Shl\RoundTable\Exceptions;

class RoundtableMalformedPayloadException extends \Exception
{
    public function __construct($payload = "", $code = 400, \Throwable $previous = null)
    {
        parent::__construct("Malformed payload: $payload", $code, $previous);
    }

}
