<?php

namespace Shl\RoundTable\Endpoints;

use Carbon\Carbon;
use Shl\RoundTable\Enums\RoundtableUri;
use Shl\RoundTable\Responses\CustomerStockVerifyResponse;

class CustomerStocksEndpoint extends Endpoint
{
    public function verify(array $customers, array $symbols, Carbon $onDate = null): CustomerStockVerifyResponse
    {
        $payload = [
            'customers' => $customers,
            'symbols' => $symbols,
            'on_date' => $onDate instanceof Carbon ? $onDate->format('Y-m-d') : null
        ];

        $response = $this->client->post(RoundtableUri::CUSTOMER_VERIFY_STOCKS, $payload);

        return new CustomerStockVerifyResponse($response);
    }
}
