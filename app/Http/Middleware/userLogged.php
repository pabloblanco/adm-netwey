<?php

namespace App\Http\Middleware;

use Closure;

class userLogged
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
        $user = session('user');
        
        if(empty($user) && !$request->is('/') && !$request->is('login') && !$request->is('logout') && !$request->is('view/reports/downloads-file/*') && !$request->is('api/user/update-pass')){
            if($request->ajax())
                return response()->json(['error' => 'Not Found'], 404);
            else
                return redirect('login')->with('err', 'No hay una sesión iniciada');
        }else{
            return $next($request);
        }
    }
}
