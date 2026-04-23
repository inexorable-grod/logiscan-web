<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:ti_admin,supervisor,gerente_ops,mensajero',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_active' => true,
            'force_password_change' => true,
        ]);

        AuditService::log('USER_CREATED', 'users', $user->id, null, auth()->id());

        return redirect()->back()->with('success', 'Usuario creado correctamente.');
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required|string|in:ti_admin,supervisor,gerente_ops,mensajero',
        ]);

        $user = User::findOrFail($id);
        $oldValues = $user->only(['name', 'email', 'role']);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ]);

        AuditService::log('USER_UPDATED', 'users', $user->id, $oldValues, auth()->id());

        return redirect()->back()->with('success', 'Usuario actualizado correctamente.');
    }

    public function toggleActive(string $id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_active' => !$user->is_active]);

        $action = $user->is_active ? 'USER_ACTIVATED' : 'USER_DEACTIVATED';
        AuditService::log($action, 'users', $user->id, null, auth()->id());

        return redirect()->back()->with('success', 'Estado del usuario actualizado.');
    }

    public function resetPassword(string $id)
    {
        $user = User::findOrFail($id);
        $tempPassword = Str::random(12);

        $user->update([
            'password' => Hash::make($tempPassword),
            'force_password_change' => true,
        ]);

        AuditService::log('PASSWORD_RESET', 'users', $user->id, null, auth()->id());

        return redirect()->back()->with('success', "Contraseña temporal: {$tempPassword}");
    }
}
