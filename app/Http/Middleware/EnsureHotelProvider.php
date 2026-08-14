<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHotelProvider
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_unless(
            $user
            && $user->is_active
            && $user->role === 'service_provider'
            && $user->serviceProvider?->provider_type === 'hotel',
            403
        );

        return $next($request);
    }
}
