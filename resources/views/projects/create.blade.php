@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="text-xl font-semibold text-gray-800">Create New Project</h2>
    </div>
    
    <div class="card-body">
        <form method="POST" action="{{ route('projects.store') }}">
            @csrf
            
            <div class="form-group">
                <label for="project-name" class="block text-sm font-medium text-gray-700 mb-1">
                    Project Name <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="project-name" 
                    name="name" 
                    value="{{ old('name') }}" 
                    required 
                    autofocus
                    maxlength="100"
                    placeholder="e.g., Work, Personal, Side Projects"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm"
                    aria-required="true">
                
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Project names must be unique. Maximum 50 characters.</p>
            </div>

            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 mt-6 pt-4 border-t border-gray-200">
                <a 
                    href="{{ url()->previous() ?? route('tasks.index') }}" 
                    class="w-full sm:w-auto inline-flex justify-center items-center px-5 py-2.5 border rounded-lg text-sm shadow-sm hover:shadow"
                >
                    Cancel
                </a>
                <button 
                    type="submit" 
                    class="w-full sm:w-auto inline-flex justify-center items-center px-5 py-2.5 border rounded-lg text-sm shadow-sm hover:shadow text-white bg-blue-600 hover:bg-blue-700"
                >
                    Create Project
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// Auto-focus validation errors
document.addEventListener('DOMContentLoaded', () => {
    @if($errors->any())
        const firstError = document.querySelector('.text-red-600');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            const input = firstError.previousElementSibling;
            if (input) input.focus();
        }
    @endif
});
</script>
@endpush
@endsection