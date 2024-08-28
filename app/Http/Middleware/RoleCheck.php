<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  int[]  ...$roleIds
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roleIds)
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();

        if (!in_array($user->role_id, $roleIds)) {
            return response()->json(['message' => 'You do not have permission to access this resource.'], 403);
        }

        return $next($request);
    }
}