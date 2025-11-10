<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class MaterialController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $materials = Material::query()
            ->when($user->isProfessor(), function($q) use ($user) {
                // Profesores ven materiales de sus secciones
                $q->whereHas('sections', fn($sq) => $sq->where('professor_id', $user->id));
            })
            ->when($user->isStudent(), function($q) use ($user) {
                // Estudiantes ven materiales de secciones donde están inscritos
                $q->whereHas('sections.students', fn($sq) => $sq->where('student_id', $user->id));
            })
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->latest()
            ->paginate(15);

        return Inertia::render('Materials/Index', [
            'materials' => $materials,
            'filters' => $request->only(['type']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Materials/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:video,pdf,link,document',
            'file' => 'nullable|file|max:10240', // 10MB máx
            'url' => 'nullable|url',
            'description' => 'nullable|string',
        ]);

        // Subir archivo si existe
        if ($request->hasFile('file')) {
            $validated['file_path'] = $request->file('file')->store('materials', 'public');
        }

        $material = Material::create($validated);

        return redirect()->route('materials.show', $material)
            ->with('success', 'Material creado exitosamente.');
    }

    public function show(Request $request, Material $material)
    {
        $user = $request->user();

        // Verificar acceso según rol
        if ($user->isProfessor()) {
            $hasAccess = $material->sections()->where('professor_id', $user->id)->exists();
            if (!$hasAccess) {
                abort(403, 'No tienes permiso para ver este material.');
            }
        }

        if ($user->isStudent()) {
            $hasAccess = $material->sections()
                ->whereHas('students', fn($q) => $q->where('student_id', $user->id))
                ->exists();
            if (!$hasAccess) {
                abort(403, 'No tienes acceso a este material.');
            }
        }

        $material->load(['courses', 'sections']);

        return Inertia::render('Materials/Show', [
            'material' => $material,
        ]);
    }

    public function edit(Material $material)
    {
        return Inertia::render('Materials/Edit', [
            'material' => $material,
        ]);
    }

    public function update(Request $request, Material $material)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:video,pdf,link,document',
            'file' => 'nullable|file|max:10240',
            'url' => 'nullable|url',
            'description' => 'nullable|string',
        ]);

        // Subir nuevo archivo si existe
        if ($request->hasFile('file')) {
            // Eliminar archivo anterior
            if ($material->file_path) {
                Storage::disk('public')->delete($material->file_path);
            }
            $validated['file_path'] = $request->file('file')->store('materials', 'public');
        }

        $material->update($validated);

        return redirect()->route('materials.show', $material)
            ->with('success', 'Material actualizado exitosamente.');
    }

    public function destroy(Material $material)
    {
        // Eliminar archivo asociado
        if ($material->file_path) {
            Storage::disk('public')->delete($material->file_path);
        }

        $material->delete();

        return redirect()->route('materials.index')
            ->with('success', 'Material eliminado exitosamente.');
    }
}
