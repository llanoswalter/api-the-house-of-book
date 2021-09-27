<?php

namespace App\Http\Middleware;

use Closure;
use app\Helpers\JwtAuth;

class ApiAuthMiddleware
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
        $token = $request->header('Authorization');
        $jwt = new JwtAuth();
        $checkToken = $jwt->checkToken($token);
        if ($checkToken) {

            return $next($request);
        } else {
            $data = array(
                'message'   => 'error',
                'code'      => 400,
                'message'   => 'Usuario sin identificar'
            );
            return response()->json($data, 200);
        }
    }
}