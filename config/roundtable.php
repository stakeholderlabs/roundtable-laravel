<?php

return [
    'base_url' => env('ROUNDTABLE_URL', 'https://roundtable.stakeholderlabs.com'),
    'api_url' => env('ROUNDTABLE_API_URL', 'https://api.roundtable.stakeholderlabs.com'),
    'secret_key' => env('ROUNDTABLE_SECRET_KEY'),
    'public_key' => env('ROUNDTABLE_PUBLIC_KEY'),
    'timeout' => env('ROUNDTABLE_API_TIMEOUT', 60)
];
