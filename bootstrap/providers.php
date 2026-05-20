<?php

use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    \App\Providers\RateLimiterServiceProvider::class,
];
