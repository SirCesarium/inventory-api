<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use Illuminate\Http\JsonResponse;

class AuditController extends Controller
{
    /**
     * Display a listing of the audits.
     */
    public function index(): JsonResponse
    {
        $audits = Audit::with(['user', 'auditable'])
            ->latest()
            ->paginate(15);

        return response()->json($audits, 200);
    }

    /**
     * Display the specified audit log.
     */
    public function show(Audit $audit): JsonResponse
    {
        $audit->load(['user', 'auditable']);

        return response()->json($audit, 200);
    }
}
