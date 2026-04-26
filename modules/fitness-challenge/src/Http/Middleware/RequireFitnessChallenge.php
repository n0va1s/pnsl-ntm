<?php

namespace Modules\FitnessChallenge\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireFitnessChallenge
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('fitness-challenge.enabled')) {
            abort(404);
        }

        return $next($request);
    }
}
