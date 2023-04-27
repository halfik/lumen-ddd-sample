<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;

class TrustProxiesMiddleware extends Middleware
{
    protected $proxies = '*';
}
