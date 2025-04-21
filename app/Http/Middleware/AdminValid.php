<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class AdminValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $routes = ['employees.create', 'employees.update', 'employees.destroy', 'employees.store', 'employees.edit'];

        if (in_array(Route::getCurrentRoute()->getName(), $routes)) {
            if (auth()->user()->owner) {
                return $next($request);
            } else {
                abort(Response::HTTP_FORBIDDEN);
            }
        }
        return $next($request);
    }
}
