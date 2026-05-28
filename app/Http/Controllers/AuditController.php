<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class AuditController extends Controller
{
    /**
     * Display a listing of the audits.
     */
    public function index(): JsonResponse
    {
        Gate::authorize('view-audits');

        $audits = Audit::with(['user', 'auditable'])
            ->latest()
            ->paginate($this->perPage());

        return response()->json($audits, 200);
    }

    /**
     * Display the specified audit log.
     */
    public function show(Audit $audit): JsonResponse
    {
        Gate::authorize('view-audits');

        $audit->load(['user', 'auditable']);

        return response()->json($audit, 200);
    }
}
