<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class TaskController extends Controller
{
    public function index(): View
    {
        $projects = Project::orderBy('name')->get();
        $selectedProjectId = request('project_id', $projects->first()?->id);

        $tasks = Task::when($selectedProjectId, fn($query) => $query->where('project_id', $selectedProjectId))
            ->with('project')
            ->orderBy('priority')
            ->get();

        return view('tasks.index', compact('projects', 'tasks', 'selectedProjectId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge(['name' => trim($request->name)]);

        $validatedRequest = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'project_id' => ['required', 'integer'],
        ]);

        Task::create($validatedRequest);

        return redirect()
            ->route('tasks.index', ['project_id' => $validatedRequest['project_id']])
            ->with('success', 'Task created successfully!');
    }

    public function edit(Task $task): View
    {
        $projects = Project::orderBy('name')->get();

        return view('tasks.edit', compact('task', 'projects'));
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $request->merge(['name' => trim($request->name)]);

        $validatedRequest = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'project_id' => ['required', 'integer'],
        ]);

        // Detect project change BEFORE any updates
        $projectChanged = $task->project_id !== (int) $validatedRequest['project_id'];
        $originalProjectId = $task->project_id;
        $originalPriority = $task->priority;

        DB::transaction(function () use ($task, $validatedRequest, $projectChanged, $originalProjectId, $originalPriority) {
            if ($projectChanged) {
                // ✅ CRITICAL: Calculate new priority BEFORE changing project_id
                $newPriority = Task::where('project_id', $validatedRequest['project_id'])->max('priority') ?? 0;
                $newPriority++; // Priority in destination project

                // Renumber Project A (close the gap left by moved task)
                Task::where('project_id', $originalProjectId)
                    ->where('priority', '>', $originalPriority)
                    ->decrement('priority');

                // Update task with NEW project AND NEW priority in one atomic operation
                $task->update([
                    'name' => $validatedRequest['name'],
                    'project_id' => $validatedRequest['project_id'],
                    'priority' => $newPriority,
                ]);
            } else {
                // No project change - just update name
                $task->update(['name' => $validatedRequest['name']]);
            }
        });

        return redirect()
            ->route('tasks.index', ['project_id' => $task->project_id])
            ->with('success', 'Task updated successfully!');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $projectId = $task->project_id;
        $task->delete();

        return redirect()
            ->route('tasks.index', ['project_id' => $projectId])
            ->with('success', 'Task deleted successfully!');
    }

    public function reorder(Request $request): JsonResponse
    {
        $validatedRequest = $request->validate([
            'task_ids' => ['required', 'array', 'min:1'],
            'task_ids.*' => ['required', 'integer'],
            'project_id' => ['required', 'integer'],
        ]);
        try {
            // Verify all tasks belong to the specified project
            $invalidTasks = Task::where('project_id', '!=', $validatedRequest['project_id'])
                ->whereIn('id', $validatedRequest['task_ids'])
                ->exists();

            if ($invalidTasks) {
                return response()->json([
                    'error' => 'Security violation: Tasks must belong to the specified project'
                ], 403);
            }

            // Update priorities in a transaction
            DB::transaction(function () use ($validatedRequest) {
                foreach ($validatedRequest['task_ids'] as $index => $taskId) {
                    Task::where('id', $taskId)->update(['priority' => $index + 1]);
                }
            });

            return response()->json(['success' => true]);
        } catch (Throwable $e) {
            Log::error('Task reordering failed: ' . $e->getMessage());
            return response()->json(['error' => 'Reordering failed. Please try again.'], 500);
        }
    }
}
