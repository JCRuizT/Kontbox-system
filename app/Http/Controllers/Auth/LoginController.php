<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Src\Domain\Services\AuditService;

/**
 * Controlador de autenticación.
 */
class LoginController extends Controller
{
    /**
     * Inicia sesión con credenciales (email + password).
     * En caso de éxito regenera la sesión y registra el evento en auditoría.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (auth()->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            AuditService::log('Inició sesión', null, ['email' => $request->email], AuditService::APP);
            return redirect()->intended('/');
        }

        return back()->withErrors(['email' => __('domain.error.invalid_credentials')])->onlyInput('email');
    }

    /**
     * Auditoría: registra cierre de sesión
     */
    public function logout(Request $request): RedirectResponse
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        AuditService::log('Cerró sesión', null, [], AuditService::APP);
        return redirect('/');
    }
}
