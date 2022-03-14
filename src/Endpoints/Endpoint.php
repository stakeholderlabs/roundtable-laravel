<?php

namespace Shl\RoundTable\Endpoints;

use Shl\RoundTable\Clients\Client;

abstract class Endpoint
{
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }


}
