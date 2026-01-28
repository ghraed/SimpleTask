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
use Illuminate\Support\Facades\Request;
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
        $validatedRequest = $request->validate([
            'name' => ['required', 'string', 'max:255', 'trim'],
            'project_id' => ['required', 'integer'],
        ]);

        Task::create($validatedRequest);

        return redirect()
            ->route('tasks.index', ['project_id' => $validatedRequest['project_id']])
            ->with('success', 'Task created successfully!');
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $validatedRequest = $request->validate([
            'name' => ['required', 'string', 'max:255', 'trim'],
            'project_id' => ['required', 'integer'],
        ]);

        $task->update($validatedRequest);

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
