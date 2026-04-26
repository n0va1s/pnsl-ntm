<?php

namespace Modules\Vendinha\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Vendinha\Services\VendinhaAccessService;
use Symfony\Component\HttpFoundation\Response;

class RequireVendinhaAccess
{
    public function __construct(private readonly VendinhaAccessService $access) {}

    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($this->access->canAccess($request->user()), 403);

        return $next($request);
    }
}
