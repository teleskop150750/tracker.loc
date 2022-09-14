<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Cache Stores
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the cache "stores" for your application as
    | well as their drivers. You may even define multiple stores for the
    | same cache driver to group types of items stored in your caches.
    |
    */
    'stores' => [
        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache'),
        ],
    ],
];
