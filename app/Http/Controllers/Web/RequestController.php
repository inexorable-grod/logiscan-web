<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ClientRequest;
use App\Services\RequestResolutionService;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    protected RequestResolutionService $resolutionService;

    public function __construct(RequestResolutionService $resolutionService)
    {
        $this->resolutionService = $resolutionService;
    }

    public function index(Request $request)
    {
        $query = ClientRequest::with(['requestedBy', 'center']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('request_type', $request->type);
        }

        $requests = $query->orderByDesc('created_at')->paginate(20);
        $types = ClientRequest::distinct()->pluck('request_type')->filter()->values();
        return view('requests.index', compact('requests', 'types'));
    }

    public function approve(Request $request, string $id)
    {
        ClientRequest::findOrFail($id);

        $result = $this->resolutionService->resolve($id, auth()->id(), 'approved', $request->input('notes'));

        if (!$result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        return redirect()->back()->with('success', 'Solicitud aprobada correctamente.');
    }

    public function reject(Request $request, string $id)
    {
        ClientRequest::findOrFail($id);

        $result = $this->resolutionService->resolve($id, auth()->id(), 'rejected', $request->input('notes'));

        if (!$result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        return redirect()->back()->with('success', 'Solicitud rechazada correctamente.');
    }
}
