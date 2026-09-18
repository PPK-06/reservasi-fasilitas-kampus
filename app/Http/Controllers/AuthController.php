<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * P3 — tampilkan form login.
     */
    public function create(): View
    {
        return view('login');
    }
}
