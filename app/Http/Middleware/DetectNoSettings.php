<?php

namespace App\Http\Middleware;

use App\Preference;
use Closure;
use Illuminate\Support\Facades\Auth;

class DetectNoSettings
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $user = $request->user();
            $prefsCount = Preference::all()->count();
            if ($prefsCount < 1) {
                if ($user->isAdmin) {
                    $request->session()->flash('alert-warning', 'The application\'s preferences have not been filled out.');
                    if ($request->route()->getName() != 'preferences' && $request->route()->uri() != 'logout') {
                        return redirect()->route('preferences');
                    }
                } else {
                    if ($request->route()->getName() != 'maintenance' && $request->route()->uri() != 'logout') {
                        return redirect()->route('maintenance');
                    }
                }
            }
        }
        return $next($request);
    }
}
