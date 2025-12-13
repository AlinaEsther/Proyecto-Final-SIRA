<?php

namespace App\Http\Controllers;

use App\Models\MaterialRequest;
use App\Models\Material;
use App\Models\Recommendation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaterialRequestController extends Controller
{
    /**
     * Crear una nueva solicitud de material
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'recommendation_id' => 'nullable|exists:recommendations,id',
            'title' => 'required|string|max:255',
            'author' => 'nullable|string|max:255',
            'type_requested' => 'required|in:video,pdf,link,document',
            'description' => 'nullable|string',
            'url' => 'nullable|url',
        ]);

        // Verificar si ya existe una solicitud pendiente o aprobada para este título por el mismo usuario
        $existingRequest = MaterialRequest::where('requested_by', auth()->id())
            ->where('title', $validated['title'])
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existingRequest) {
            return redirect()->back()->with('alert', [
                'type' => 'warning',
                'message' => 'Ya has solicitado este material anteriormente.'
            ]);
        }

        // Verificar si ya existe el material en el sistema
        $existingMaterial = Material::where('title', $validated['title'])->first();
        if ($existingMaterial) {
            // Si existe el material, actualizar la recomendación a completada
            if (isset($validated['recommendation_id'])) {
                Recommendation::where('id', $validated['recommendation_id'])
                    ->update([
                        'material_id' => $existingMaterial->id,
                        'status' => 'completed'
                    ]);
            }

            return redirect()->back()->with('alert', [
                'type' => 'info',
                'message' => 'Este material ya está disponible en el sistema.'
            ]);
        }

        $materialRequest = MaterialRequest::create([
            ...$validated,
            'requested_by' => auth()->id(),
            'status' => 'pending',
        ]);

        // Actualizar la recomendación con el material_request_id y cambiar status a 'pending'
        if (isset($validated['recommendation_id'])) {
            Recommendation::where('id', $validated['recommendation_id'])
                ->update([
                    'material_request_id' => $materialRequest->id,
                    'status' => 'pending' // Ahora SÍ está pendiente de aprobación
                ]);
        }

        return redirect()->back()->with('alert', [
            'type' => 'success',
            'message' => 'Solicitud de material enviada exitosamente.'
        ]);
    }

    /**
     * Aprobar una solicitud de material
     * Crea el material automáticamente y lo asocia con la solicitud
     */
    public function approve(Request $request, MaterialRequest $materialRequest)
    {
        if ($materialRequest->status !== 'pending') {
            return redirect()->back()->with('alert', [
                'type' => 'error',
                'message' => 'Solo se pueden aprobar solicitudes pendientes.'
            ]);
        }

        // Validar los datos editados por el admin
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'nullable|string|max:255',
            'url' => 'required|url',
            'description' => 'nullable|string',
            'admin_notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($materialRequest, $validated) {
            // Crear el material con los datos editados
            $material = Material::create([
                'title' => $validated['title'],
                'author' => $validated['author'] ?? null,
                'type' => $materialRequest->type_requested,
                'url' => $validated['url'],
                'description' => $validated['description'] ?? 'Material aprobado desde solicitud',
            ]);

            // Actualizar la solicitud
            $materialRequest->update([
                'status' => 'approved',
                'material_id' => $material->id,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'admin_notes' => $validated['admin_notes'] ?? null,
            ]);

            // Si la solicitud viene de una recomendación IA, actualizar la recomendación con el material
            if ($materialRequest->recommendation_id) {
                Recommendation::where('id', $materialRequest->recommendation_id)
                    ->update([
                        'material_id' => $material->id,
                        'status' => 'completed' // Cambiar estado a completado cuando se aprueba el material
                    ]);
            }
        });

        return redirect()->back()->with('alert', [
            'type' => 'success',
            'message' => 'Solicitud aprobada y material creado exitosamente.'
        ]);
    }

    /**
     * Rechazar una solicitud de material
     */
    public function reject(Request $request, MaterialRequest $materialRequest)
    {
        if ($materialRequest->status !== 'pending') {
            return redirect()->back()->with('alert', [
                'type' => 'error',
                'message' => 'Solo se pueden rechazar solicitudes pendientes.'
            ]);
        }

        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $materialRequest->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'admin_notes' => $validated['admin_notes'] ?? null,
        ]);

        return redirect()->back()->with('alert', [
            'type' => 'success',
            'message' => 'Solicitud rechazada.'
        ]);
    }

    /**
     * Obtener todas las solicitudes pendientes con sus relaciones
     */
    public function pending()
    {
        $pendingRequests = MaterialRequest::with([
            'requester.person',
            'recommendation',
        ])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($pendingRequests);
    }
}
