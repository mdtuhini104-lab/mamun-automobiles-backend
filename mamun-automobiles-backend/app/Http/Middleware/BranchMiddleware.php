<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Config;

class BranchMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            
            // Check if X-Branch-ID header is present
            $branchId = $request->header('X-Branch-ID') ?: $request->input('branch_id');
            
            if ($branchId) {
                // Check if user is Super Admin or Manager to allow branch switching
                if ($user->hasRole('Super Admin') || $user->hasRole('Manager')) {
                    Config::set('app.current_branch_id', (int)$branchId);
                } else {
                    // For regular users, ensure they cannot leak or access another branch
                    if ($user->branch_id && (int)$user->branch_id !== (int)$branchId) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Unauthorized: Cross-branch access is prohibited.'
                        ], 403);
                    }
                    Config::set('app.current_branch_id', (int)$user->branch_id);
                }
            } else if ($user->branch_id) {
                Config::set('app.current_branch_id', (int)$user->branch_id);
            }
        }

        return $next($request);
    }
}
