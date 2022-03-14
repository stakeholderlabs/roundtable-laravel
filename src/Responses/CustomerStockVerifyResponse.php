<?php

namespace Shl\RoundTable\Responses;

use Illuminate\Support\Collection;
use Shl\RoundTable\Clients\Response;

class CustomerStockVerifyResponse
{
    protected $customers;

    public function __construct(Response $response)
    {
        $customers = $response->get('data');

        $this->customers = new Collection($customers);
    }

    public function customerHasSymbol(int $customerId, string $symbol): bool
    {
        return $this->customers->filter(function (array $item) use ($customerId, $symbol) {
            if ($item['customer_id'] === $customerId) {
                foreach ($item['stocks'] as $stock) {
                    if ($stock['symbols'] === $symbol) {
                        return $stock['holds'] === true;
                    }
                }
            }

            return false;
        })->isNotEmpty();
    }
}
