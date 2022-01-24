<?php

namespace Shl\RoundTable\Entity;

use Illuminate\Support\Arr;

class RoundtablePayloadEntity
{
    protected $customerId;

    public function __construct(array $data)
    {
        $this->customerId = Arr::get($data, 'customer_id');
    }

    public function getCustomerId(): ?int
    {
        return $this->customerId;
    }
}
