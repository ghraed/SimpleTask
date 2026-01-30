<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReorderTasksRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
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

    public function store(StoreTaskRequest $request): RedirectResponse
    {
        // Auto-set priority for new tasks (bottom of project)
        $priority = Task::where('project_id', $request->project_id)->max('priority') + 1;

        Task::create([
            'name' => $request->name,
            'project_id' => $request->project_id,
            'priority' => $priority,
        ]);

        return redirect()
            ->route('tasks.index', ['project_id' => $request->project_id])
            ->with('success', 'Task "' . $request->name . '" created successfully!');
    }

    public function edit(Task $task): View
    {
        $projects = Project::orderBy('name')->get();

        return view('tasks.edit', compact('task', 'projects'));
    }

    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        $projectChanged = $task->project_id !== $request->project_id;
        $originalProjectId = $task->project_id;
        $originalPriority = $task->priority;

        DB::transaction(function () use ($task, $request, $projectChanged, $originalProjectId, $originalPriority) {
            if ($projectChanged) {
                // Calculate new priority in DESTINATION project BEFORE changing project_id
                $newPriority = Task::where('project_id', $request->project_id)->max('priority') ?? 0;
                $newPriority++;

                // Close gap in ORIGINAL project
                Task::where('project_id', $originalProjectId)
                    ->where('priority', '>', $originalPriority)
                    ->decrement('priority');

                // Update task with new project and priority
                $task->update([
                    'name' => $request->name,
                    'project_id' => $request->project_id,
                    'priority' => $newPriority,
                ]);
            } else {
                // Only update name when project unchanged
                $task->update(['name' => $request->name]);
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

    public function reorder(ReorderTasksRequest $request): JsonResponse
    {
        try {
            // Verify all tasks belong to specified project
            $invalidTasks = Task::where('project_id', '!=', $request->project_id)
                ->whereIn('id', $request->task_ids)
                ->exists();

            if ($invalidTasks) {
                return response()->json([
                    'error' => 'Security violation: Tasks must belong to the specified project'
                ], 403);
            }

            // Update priorities atomically
            DB::transaction(function () use ($request) {
                foreach ($request->task_ids as $index => $taskId) {
                    Task::where('id', $taskId)->update(['priority' => $index + 1]);
                }
            });

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::error('Task reordering failed: ' . $e->getMessage());
            return response()->json(['error' => 'Reordering failed. Please try again.'], 500);
        }
    }
}
