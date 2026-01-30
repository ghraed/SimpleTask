<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
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

    public function store(StoreProjectRequest $request)
    {
        Project::create($request->validated());


        Log::info('created');
        return redirect()
            ->route('tasks.index')
            ->with('success', 'Project created successfully!');
    }
}
