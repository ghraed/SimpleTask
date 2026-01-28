<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function create(): View
    {
        return view('projects.create');
    }

    public function store(Request $request)
    {
        $validatedRequest = $request->validate([
            'name' => ['required', 'string', 'max:255', 'trim'],
            'project_id' => ['required', 'integer'],
        ]);


        Project::create($validatedRequest);

        return redirect()
            ->route('tasks.index')
            ->with('success', 'Project created successfully!');
    }
}
