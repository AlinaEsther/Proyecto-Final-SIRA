<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class MaterialController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $user = $request->user();

        $materials = Material::query()
            ->when($user->isProfessor(), function ($query) use ($user) {
                // Profesores solo ven materiales de SUS secciones
                $query->whereHas('sections', fn($sq) => $sq->where('professor_id', $user->id));
            })
            ->when($user->isStudent(), function ($query) use ($user) {
                // Estudiantes solo ven materiales de secciones donde están inscritos
                $query->whereHas('sections.students', fn($sq) => $sq->where('student_id', $user->id));
            })
            ->when($search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->latest()
            ->get();

        return Inertia::render('Materials/Index', [
            'materials' => $materials,
            'filters' => $request->only(['search']),
        ]);
    }

    public function show(Material $material)
    {
        return Inertia::render('Materials/Show', [
            'material' => $material
        ]);
    }

    public function create()
    {
        return Inertia::render('Materials/Create');
    }

    public function store(Request $request)
    {
        // Validación dinámica según el tipo de material
        $rules = [
            'title' => 'required|string|max:255',
            'type' => 'required|in:video,pdf,link,document',
            'description' => 'nullable|string',
        ];

        // Validación específica por tipo
        switch ($request->type) {
            case 'pdf':
                $rules['file'] = 'required|file|mimes:pdf|max:25600';
                break;
            case 'video':
                // Los videos pueden ser URL o archivo
                $rules['url'] = 'nullable|url';
                $rules['file'] = 'nullable|file|mimes:mp4,avi,mov,wmv,flv,mkv|max:102400'; // 100MB max para videos
                // Al menos uno debe estar presente
                $request->validate([
                    'url' => 'required_without:file',
                    'file' => 'required_without:url',
                ]);
                break;
            case 'link':
                $rules['url'] = 'required|url';
                break;
            case 'document':
                // Documentos: zona genérica para cualquier archivo
                $rules['file'] = 'required|file|mimes:doc,docx,txt,xls,xlsx,ppt,pptx,pdf,jpg,jpeg,png,gif|max:25600';
                break;
        }

        $validated = $request->validate($rules);

        $material = new Material($validated);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('materials', 'public');
            $material->file_path = $path;
            $material->original_filename = $file->getClientOriginalName();
        }

        $material->save();

        return redirect()->route('materials.index')
            ->with('alert', [
                'type' => 'success',
                'message' => 'Material creado exitosamente.'
            ]);
    }

    public function edit(Material $material)
    {
        return Inertia::render('Materials/Edit', [
            'material' => $material
        ]);
    }

    public function update(Request $request, Material $material)
    {
        // Validación dinámica según el tipo de material
        $rules = [
            'title' => 'required|string|max:255',
            'type' => 'required|in:video,pdf,link,document',
            'description' => 'nullable|string',
        ];

        // Validación específica por tipo
        $type = $request->input('type');
        switch ($type) {
            case 'pdf':
                $rules['file'] = 'nullable|file|mimes:pdf|max:25600';
                break;
            case 'video':
                $rules['url'] = 'nullable|url';
                $rules['file'] = 'nullable|file|mimes:mp4,avi,mov,wmv,flv,mkv|max:102400';
                break;
            case 'link':
                $rules['url'] = 'nullable|url';
                break;
            case 'document':
                $rules['file'] = 'nullable|file|mimes:doc,docx,txt,xls,xlsx,ppt,pptx,pdf,jpg,jpeg,png,gif|max:25600';
                break;
        }

        $validated = $request->validate($rules);

        $material->fill($request->except('file'));

        if ($request->hasFile('file')) {
            // Delete old file if exists
            if ($material->file_path) {
                Storage::disk('public')->delete($material->file_path);
            }
            $file = $request->file('file');
            $path = $file->store('materials', 'public');
            $material->file_path = $path;
            $material->original_filename = $file->getClientOriginalName();
        }

        $material->save();

        return redirect()->route('materials.index')
            ->with('alert', [
                'type' => 'success',
                'message' => 'Material actualizado exitosamente.'
            ]);
    }

    public function destroy(Material $material)
    {
        if ($material->file_path) {
            Storage::disk('public')->delete($material->file_path);
        }

        $material->delete();

        return redirect()->route('materials.index')
            ->with('alert', [
                'type' => 'success',
                'message' => 'Material eliminado exitosamente.'
            ]);
    }

    public function download(Material $material)
    {
        // Solo descargar si tiene un archivo
        if (!$material->file_path) {
            abort(404, 'No hay archivo disponible para descargar');
        }

        // Verificar que el archivo existe en storage
        if (!Storage::disk('public')->exists($material->file_path)) {
            abort(404, 'Archivo no encontrado');
        }

        return Storage::disk('public')->download(
            $material->file_path,
            $material->original_filename ?? basename($material->file_path)
        );
    }
}
