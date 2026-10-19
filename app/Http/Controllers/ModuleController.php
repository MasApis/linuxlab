<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ModuleController extends Controller
{
    /**
     * Menampilkan daftar seluruh modul pembelajaran yang aktif (FR-MOD-01).
     * Pada Fase 2, persentase penyelesaian ditampilkan 0% secara jujur
     * karena pelacakan progres pengguna baru diaktifkan pada Fase 5 (FR-PROG-02).
     */
    public function index(Request $request): Response
    {
        $modules = Module::query()
            ->where('is_published', true)
            ->orderBy('order')
            ->withCount('lessons')
            ->get()
            ->map(function (Module $module) {
                return [
                    'id' => $module->id,
                    'title' => $module->title,
                    'slug' => $module->slug,
                    'description' => $module->description,
                    'order' => $module->order,
                    'lessons_count' => $module->lessons_count,
                    'completion_percentage' => 0, // Keadaan awal jujur sebelum Fase 5
                ];
            });

        return Inertia::render('Modules/Index', [
            'modules' => $modules,
        ]);
    }

    /**
     * Menampilkan detail suatu modul beserta daftar pelajarannya berurutan (FR-MOD-01).
     */
    public function show(Module $module): Response
    {
        abort_unless($module->is_published, 404);

        $module->load(['lessons' => function ($query) {
            $query->orderBy('order');
        }]);

        return Inertia::render('Modules/Show', [
            'module' => [
                'id' => $module->id,
                'title' => $module->title,
                'slug' => $module->slug,
                'description' => $module->description,
                'order' => $module->order,
                'completion_percentage' => 0, // Keadaan awal jujur
                'lessons' => $module->lessons->map(function ($lesson) {
                    return [
                        'id' => $lesson->id,
                        'title' => $lesson->title,
                        'slug' => $lesson->slug,
                        'order' => $lesson->order,
                    ];
                }),
            ],
        ]);
    }
}
