<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::published()->orderBy('sort_order')->latest('updated_at');

        if ($status = $request->get('estado')) {
            if (in_array($status, ['planificado', 'en_curso', 'completado'])) {
                $query->where('status', $status);
            }
        }

        $projects = $query->paginate(12)->withQueryString();

        $counts = [
            'all' => Project::published()->count(),
            'planificado' => Project::published()->where('status', 'planificado')->count(),
            'en_curso' => Project::published()->where('status', 'en_curso')->count(),
            'completado' => Project::published()->where('status', 'completado')->count(),
        ];

        return view('pages.projects.index', [
            'projects' => $projects,
            'activeStatus' => $status,
            'counts' => $counts,
        ]);
    }

    public function show(string $slug)
    {
        $project = Project::where('slug', $slug)->published()->firstOrFail();

        $related = Project::published()
            ->where('id', '!=', $project->id)
            ->where('status', $project->status)
            ->limit(3)
            ->get();

        return view('pages.projects.show', [
            'project' => $project,
            'related' => $related,
        ]);
    }
}
