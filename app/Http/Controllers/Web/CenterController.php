<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\OperationCenter;
use App\Models\CenterUser;
use App\Services\AuditService;
use Illuminate\Http\Request;

class CenterController extends Controller
{
    public function index()
    {
        $centers = OperationCenter::withCount('assignments')->orderBy('name')->paginate(20);
        return view('admin.centers.index', compact('centers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:operation_centers,code',
            'address' => 'nullable|string|max:500',
        ]);

        $center = OperationCenter::create([
            'name' => $request->name,
            'code' => $request->code,
            'address' => $request->address,
            'is_active' => true,
            'created_by' => auth()->id(),
        ]);

        AuditService::log('CENTER_CREATED', 'operation_centers', $center->id, null, auth()->id());

        return redirect()->back()->with('success', 'Centro operativo creado correctamente.');
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:operation_centers,code,' . $id,
            'address' => 'nullable|string|max:500',
        ]);

        $center = OperationCenter::findOrFail($id);
        $oldValues = $center->only(['name', 'code', 'address']);

        $center->update([
            'name' => $request->name,
            'code' => $request->code,
            'address' => $request->address,
        ]);

        AuditService::log('CENTER_UPDATED', 'operation_centers', $center->id, $oldValues, auth()->id());

        return redirect()->back()->with('success', 'Centro operativo actualizado correctamente.');
    }

    public function assign(Request $request, string $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $center = OperationCenter::findOrFail($id);

        CenterUser::firstOrCreate([
            'center_id' => $center->id,
            'user_id' => $request->user_id,
        ]);

        AuditService::log('CENTER_USER_ASSIGNED', 'operation_centers', $center->id, [
            'user_id' => $request->user_id,
        ], auth()->id());

        return redirect()->back()->with('success', 'Usuario asignado al centro correctamente.');
    }
}
