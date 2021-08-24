<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermissions
{
    public function handle(Request $request, Closure $next)
    {
        $roles = $request->user()->roles()->where('institution_id', $request->institution)
            ->where('roles.id', $request->role)->get();

        if (sizeof($roles) === 0) {
            return response()->json([
                'data' => $roles,
                'msg' => [
                    'summary' => 'No tiene un rol asignado (check-permissions)',
                    'detail' => 'Comunicate con el administrador',
                    'code' => '403'
                ]
            ], 403);
        }
        return $next($request);
    }
}
