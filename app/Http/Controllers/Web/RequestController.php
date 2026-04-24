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
        $clientRequest = ClientRequest::findOrFail($id);

        $this->resolutionService->resolve($clientRequest, 'approved', auth()->user(), $request->input('notes'));

        return redirect()->back()->with('success', 'Solicitud aprobada correctamente.');
    }

    public function reject(Request $request, string $id)
    {
        $clientRequest = ClientRequest::findOrFail($id);

        $this->resolutionService->resolve($clientRequest, 'rejected', auth()->user(), $request->input('notes'));

        return redirect()->back()->with('success', 'Solicitud rechazada correctamente.');
    }
}
