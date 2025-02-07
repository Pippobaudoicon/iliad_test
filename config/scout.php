<?php

use App\Models\Order;

return [
    'driver' => env('SCOUT_DRIVER', 'meilisearch'),

    'queue' => false,

    'meilisearch' => [
        'host' => env('MEILISEARCH_HOST', 'http://localhost:7700'),
        'key' => env('MEILISEARCH_KEY', null),
        'index-settings' => [
            Order::class => [
                'filterableAttributes' => ['customer_name', 'description'],
                'sortableAttributes' => ['created_at', 'customer_name'],
                'searchableAttributes' => ['customer_name', 'description', 'products.name', 'products.description']
            ],
        ],
    ],
];
