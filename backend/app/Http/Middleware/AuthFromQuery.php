<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthFromQuery
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->has('token') && ! $request->hasHeader('Authorization')) {
            \Log::info('AuthFromQuery: Token found in query string', ['token' => $request->query('token')]);
            $request->headers->set('Authorization', 'Bearer ' . $request->query('token'));
            \Log::info('AuthFromQuery: Authorization header set', ['header' => $request->header('Authorization')]);
        } else {
            \Log::info('AuthFromQuery: No token in query or Authorization header already present', [
                'has_token'       => $request->has('token'),
                'has_auth_header' => $request->hasHeader('Authorization'),
            ]);
        }

        return $next($request);
    }
}
