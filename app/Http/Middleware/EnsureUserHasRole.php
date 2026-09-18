<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Periksa status akun lebih dulu, baru role.
     *
     * Dipasang setelah middleware `auth`, sehingga user selalu ada. Status
     * diperiksa di setiap request karena akun yang di-suspend saat sedang
     * login masih memegang session yang hidup.
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if ($user->status !== 'verified') {
            $message = $user->statusRejectionMessage();

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['email' => $message]);
        }

        if ($user->role !== $role) {
            abort(403);
        }

        return $next($request);
    }
}
