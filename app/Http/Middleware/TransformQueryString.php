<?php

namespace App\Http\Middleware;

use Closure;

class TransformQueryString
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        foreach ($request->all() as $key => $value) {
            if (strtolower(trim($value)) === 'null' || strtolower(trim($value)) === '') {
                $request->merge([$key => null]);
            }

            if (strtolower(trim($value)) === 'true') {
                $request->merge([$key => true]);
            }

            if (strtolower(trim($value)) === 'false') {
                $request->merge([$key => false]);
            }
        }
        
        return $next($request);
    }
}
