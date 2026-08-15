<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRestaurantProvider
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(
            $request->user()
            && $request->user()->is_active
            && $request->user()->role === 'service_provider'
            && $request->user()->serviceProvider?->provider_type === 'restaurant',
            403
        );

        return $next($request);
    }
}
