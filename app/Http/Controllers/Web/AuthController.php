<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            // Only allow web login for admin roles
            if (!in_array($user->role, ['ti_admin', 'supervisor', 'gerente_ops'])) {
                Auth::logout();
                return back()->with('error', 'No tiene permisos para acceder al panel web.');
            }

            AuditService::log('WEB_LOGIN', 'users', $user->id, null, $user->id);

            if ($user->force_password_change) {
                return redirect('/password/change');
            }
            return redirect('/dashboard');
        }

        return back()->with('error', 'Credenciales incorrectas.')->withInput($request->only('email'));
    }

    public function logout(Request $request)
    {
        AuditService::log('WEB_LOGOUT', 'users', Auth::id(), null, Auth::id());
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function showChangePassword()
    {
        return view('auth.change-password');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'La contraseña actual es incorrecta.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'force_password_change' => false,
        ]);

        AuditService::log('PASSWORD_CHANGED', 'users', $user->id);
        return redirect('/dashboard')->with('success', 'Contraseña actualizada correctamente.');
    }
}
