@extends('layouts.app')

@section('title', 'Edit Task')

@section('content')
<div class="max-w-3xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <a href="{{ url()->previous() ?? route('tasks.index', ['project_id' => $task->project_id]) }}" 
           class="inline-flex items-center text-gray-600 mb-3 group">
            <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Tasks
        </a>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Edit Task</h1>
                <p class="mt-1 text-gray-500 flex items-center">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                        
                        {{ $task->project->name }}
                    </span>
                    <span class="mx-2 text-gray-300">•</span>
                    <span class="text-sm">Created {{ $task->created_at->format('M j, Y') }}</span>
                </p>
            </div>
            <div class="mt-4 sm:mt-0 flex-shrink-0">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-amber-100 text-amber-800">
                    Priority #{{ $task->priority }}
                </span>
            </div>
        </div>
    </div>

    <!-- Edit Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Form Header -->
        <div class="px-6 py-5">
            <h2 class="text-lg font-semibold text-gray-800">Task Details</h2>
        </div>

        <!-- Form -->
        <form method="POST" action="{{ route('tasks.update', $task) }}" class="p-6">
            @csrf
            @method('PUT')

            <!-- Task Name -->
            <div class="mb-6">
                <label for="task-name" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Task Name <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <input 
                        type="text" 
                        id="task-name" 
                        name="name" 
                        value="{{ old('name', $task->name) }}" 
                        required 
                        maxlength="255"
                        autofocus
                        class="w-full pl-3 pr-10 py-2.5 border border-gray-300 rounded-lg appearance-none bg-white"
                        placeholder="e.g., Prepare quarterly report">
                </div>
                @error('name')
                    <p class="mt-1.5 text-sm text-rose-600 flex items-center">
                        
                        {{ $message }}
                    </p>
                @enderror
                <p class="mt-2 p-3 text-xs text-gray-400">Maximum 255 characters • Be specific and actionable</p>
            </div>

            <!-- Project Selection -->
            <div class="mb-7">
                <label for="project-select" class="block text-sm font-medium text-gray-700 mb-1.5">
                    Project Assignment
                </label>
                <div class="relative">
                    <select 
                        id="project-select" 
                        name="project_id" 
                        class="w-full pl-3 pr-10 py-2.5 border border-gray-300 rounded-lg appearance-none bg-white"
                        data-original-project="{{ $task->project_id }}"
                    >
                        @foreach($projects as $project)
                            <option 
                                value="{{ $project->id }}" 
                                {{ old('project_id', $task->project_id) == $project->id ? 'selected' : '' }}
                                @disabled($projects->count() === 1 && $project->id === $task->project_id)
                            >
                                {{ $project->name }}
                                @if($project->id === $task->project_id && $projects->count() > 1)
                                    (current)
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                        
                    </div>
                </div>
                @error('project_id')
                    <p class="mt-1.5 text-sm text-rose-600 flex items-center">
                        
                        {{ $message }}
                    </p>
                @enderror
                <div class="mt-2 p-3">
                    <p class="mt-1 text-xs text-gray-400">
                        
                        <span>Changing projects will move this task to the bottom of the new project's list</span>
                    </p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-5 border-t border-gray-200">
                <a 
                    href="{{ url()->previous() ?? route('tasks.index', ['project_id' => $task->project_id]) }}" 
                    class="w-full sm:w-auto inline-flex justify-center items-center px-5 py-2.5 border rounded-lg text-sm shadow-sm hover:shadow"
                >
                    Cancel
                </a>
                <button 
                    type="submit" 
                    class="w-full sm:w-auto inline-flex justify-center items-center px-5 py-2.5 border rounded-lg text-sm shadow-sm hover:shadow text-white bg-blue-600 hover:bg-blue-700"
                >
                    
                    Save Changes
                </button>
            </div>
        </form>

        <!-- Task Metadata -->
        <div class="border-t border-gray-200 bg-gray-50 px-6 py-5">
            {{-- <h3 class="text-sm font-medium text-gray-700 mb-3">Task Metadata</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div class="bg-white p-3.5 rounded-xl border border-gray-200">
                    <p class="text-xs text-gray-500 mb-1">Last Updated</p>
                    <p class="font-medium text-gray-900">{{ $task->updated_at->diffForHumans() }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $task->updated_at->format('M j, Y • g:i A') }}</p>
                </div>
                <div class="bg-white p-3.5 rounded-xl border border-gray-200">
                    <p class="text-xs text-gray-500 mb-1">Current Position</p>
                    <p class="font-medium text-gray-900">Priority #{{ $task->priority }} in "{{ $task->project->name }}"</p>
                    <p class="text-xs text-gray-400 mt-0.5">Drag to reorder in task list</p>
                </div>
            </div> --}}
            <form method="POST" action="{{ route('tasks.destroy', $task) }}" 
                          onsubmit="return confirm('Are you absolutely sure? This will permanently delete the task and cannot be undone.');"
                    class="flex flex-shrink-0 justify-end pt-5">
                @csrf
                @method('DELETE')
                <button 
                    type="submit" 
                    class="inline-flex items-center px-3.5 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-rose-600 hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 transition-colors shadow-sm"
                >
                    
                    Delete Task
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const projectSelect = document.getElementById('project-select');
    const originalProjectId = projectSelect.dataset.originalProject;
    

    projectSelect.addEventListener('change', function() {
        if (this.value !== originalProjectId && this.value !== '') {
            const newProjectName = this.options[this.selectedIndex].text
                .replace('(current)', '').trim();
            
            if (!confirm(`This will move the task to the bottom of "${newProjectName}".\n\nContinue?`)) {
                this.value = originalProjectId;
                return false;
            }
        }
    });
});
</script>
@endpush
@endsection