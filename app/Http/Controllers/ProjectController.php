<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function create(): View
    {
        return view('projects.create');
    }

    public function store(Request $request)
    {
        $request->merge(['name' => trim($request->name)]);

        $validatedRequest = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        Project::create($validatedRequest);
        Log::info('created');
        return redirect()
            ->route('tasks.index')
            ->with('success', 'Project created successfully!');
    }
}
