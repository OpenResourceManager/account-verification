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

        $user = $request->user();
        $prefsCount = Preference::all()->count();
        if ($user->isAdmin && $prefsCount < 1) {
            $request->session()->flash('alert-warning', 'The application\'s preferences have not been filled out.');
            if ($request->route()->getName() != 'preferences') {
                return redirect()->route('preferences');
            }
        }

        return $next($request);
    }
}
