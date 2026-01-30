@extends('layouts.app')

@section('title', 'Tasks')

@section('content')
<div class="max-w-5xl mx-auto">
    
    <!-- Header Section -->
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="md:text-3xl font-bold text-gray-900">Task Manager</h1>
                <p class="mt-1 text-gray-500">Organize and prioritize your task</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <a href="{{ route('projects.create') }}" 
                   class="w-full sm:w-auto inline-flex justify-center items-center px-5 py-2.5 border rounded-lg text-sm shadow-sm hover:shadow">
                    New Project
                </a>
            </div>
        </div>
    </div>

    <!-- Project Filter Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="mb-3 sm:mb-0">
                    <label for="project-select" class="block text-sm font-medium text-gray-700 mb-1">Filter by Project</label>
                    <select id="project-select" class="w-full sm:w-64 pl-3 pr-10 py-2 border border-gray-300 rounded-lg  appearance-none text-sm">
                        <option value="{{ route('tasks.index') }}" {{ !$selectedProjectId ? 'selected' : '' }} disabled>
                            All Projects
                        </option>
                        @foreach($projects as $project)
                            <option value="{{ route('tasks.index', ['project_id' => $project->id]) }}" 
                                    {{ $selectedProjectId == $project->id ? 'selected' : '' }}>
                                {{ $project->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center">
                    <span class="text-sm text-gray-600 font-medium">
                        {{ $tasks->count() }} task{{ $tasks->count() !== 1 ? 's' : '' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Task Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 mb-6">
        <div class="px-6 py-5">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Add New Task</h2>
            <form method="POST" action="{{ route('tasks.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="project_id" value="{{ $selectedProjectId }}">
                
                <div>
                    <label for="task-name" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Task Name <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            id="task-name" 
                            name="name" 
                            required 
                            placeholder="e.g., Complete quarterly report, Review PR #42..."
                            class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg transition-all"
                            aria-label="Task name">
                    </div>
                    @error('name')
                        <p class="mt-1.5 text-sm text-rose-600 flex items-center">
                            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <button 
                        type="submit" 
                        class="w-full sm:w-auto inline-flex justify-center items-center px-5 py-2.5 border rounded-lg text-sm shadow-sm hover:shadow"
                    >
                        Add Task
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tasks List -->
    @if($tasks->isEmpty())
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-12 text-center">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">No Tasks Found</h3>
            <p class="text-gray-500 mb-6">
                @if($selectedProjectId)
                    This project doesn't have any tasks yet. Start by adding one above!
                @else
                    No tasks to display. Select a project or create your first task!
                @endif
            </p>
            @if(!$selectedProjectId && $projects->isNotEmpty())
                <div class="mt-4">
                    <p class="text-sm text-gray-600 mb-3">Quick start: Select a project from the filter above</p>
                    <div class="flex flex-wrap justify-center gap-2">
                        @foreach($projects->take(3) as $project)
                            <a href="{{ route('tasks.index', ['project_id' => $project->id]) }}" 
                               class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                                {{ $project->name }}
                            </a>
                        @endforeach
                        @if($projects->count() > 3)
                            <span class="text-sm text-gray-400">+{{ $projects->count() - 3 }} more</span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    @else
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-800">Tasks</h2>
                    <p class="text-sm text-gray-500">
                        <span class="font-medium">{{ $tasks->count() }}</span> task{{ $tasks->count() !== 1 ? 's' : '' }}
                    </p>
            </div>

            <ul id="task-list" class="divide-y divide-gray-200">
                @foreach($tasks as $task)
                    <li 
                        class="task-item group flex items-center px-6 py-4 hover:bg-gray-50 transition-colors" 
                        data-id="{{ $task->id }}"
                        data-project-id="{{ $task->project_id }}"
                    >
                        <!-- Drag and drop Handle -->
                        <button 
                            type="button"
                            class="task-handle mr-4 p-1.5 text-gray-400 hover:text-indigo-600 rounded-lg cursor-grab hover:bg-gray-100 transition-colors"
                            aria-label="Drag to reorder task"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                            </svg>
                        </button>

                        <!-- Task Content (name, priority)-->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $task->name }}</p>
                                    <div class="mt-1 flex items-center text-xs text-gray-500">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                            {{ $task->project->name }}
                                        </span>
                                        <span class="mx-2">•</span>
                                        <span class="flex items-center task-priority">
                                            Priority #{{ $task->priority }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons (edit, delete) -->
                        <div class="task-actions flex items-center space-x-2 ml-4 opacity-0 group-hover:opacity-100 transition-opacity">
                            <a 
                                href="{{ route('tasks.edit', $task) }}" 
                                class="w-full sm:w-auto inline-flex justify-center items-center px-5 py-2.5 border rounded-lg text-sm shadow-sm hover:shadow"
                                aria-label="Edit task"
                            >
                                Edit
                            </a>
                            <form method="POST" action="{{ route('tasks.destroy', $task) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button 
                                    type="submit" 
                                    class="w-full sm:w-auto inline-flex justify-center items-center px-5 py-2.5 border rounded-lg text-sm shadow-sm hover:shadow bg-rose-600 text-white"
                                    onclick="return confirm('Are you sure you want to delete this task?')"
                                    aria-label="Delete task"
                                >
                                    Delete
                                </button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                <p class="text-sm text-gray-500 flex items-center">
                    <span><strong>Tip:</strong> Drag tasks using the handle to reorder them. Priority updates automatically!</span>
                </p>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Project filter navigation
    const projectSelect = document.getElementById('project-select');
    if (projectSelect) {
        projectSelect.addEventListener('change', function() {
            if (this.value) {
                window.location.href = this.value;
            }
        });
    }

    // Drag and drop initialization
    const taskList = document.getElementById('task-list');
    if (!taskList || taskList.children.length === 0) return;

    // Initialize Sortable with accessibility enhancements
    Sortable.create(taskList, {
        handle: '.task-handle',
        animation: 150,
        onEnd: async (evt) => {
            
            const taskItems = taskList.querySelectorAll('.task-item');
            const taskIds = Array.from(taskItems).map(item => item.dataset.id);
            const projectId = taskItems[0]?.dataset.projectId || '{{ $selectedProjectId }}';
            
            try {
                const response = await fetch("{{ route('tasks.reorder') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        task_ids: taskIds,
                        project_id: projectId
                    })
                });

                if (!response.ok) {
                    const data = await response.json();
                    throw new Error(data.error || 'Reordering failed');
                }
                
                // Visual feedback
                taskList.querySelectorAll('.task-item').forEach((item, index) => {
                    // change the text of last span inside item
                    const lastSpan = item.querySelectorAll('span')[2];
                    // alert("before: " + item.textContent)
                    lastSpan.textContent = "Priority #" + (index + 1);
                    // alert("after: " + item.textContent)
                    item.style.transition = 'background-color 0.3s ease';
                    item.style.backgroundColor = '#f0fdf4';
                    setTimeout(() => {
                        item.style.backgroundColor = '';
                        item.style.boxShadow = '';
                    }, 300);
                });
            } catch (error) {
                // Rollback on error
                console.error('Reorder failed:', error);
                alert(error.message || 'Failed to reorder tasks. Please refresh and try again.');
                window.location.reload();
            }
        }
    });
});
</script>
@endpush
@endsection