<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RegisterController extends Controller
{
    /**
     * P4 — tampilkan form registrasi mandiri.
     */
    public function create(): View
    {
        abort(501);
    }

    /**
     * P4 — proses registrasi mandiri, akun baru berstatus `pending`.
     */
    public function store(): RedirectResponse
    {
        abort(501);
    }
}
