<?php
// Custom auth middleware
// Uses standard Authenticate middleware, but if auth fails allows request to continue without a user
namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;

class OptionalAuth extends Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, ...$guards)
    {
        try {
            $this->authenticate($request, $guards);
        } catch (\Exception $e){}

        return $next($request);
    }
}
