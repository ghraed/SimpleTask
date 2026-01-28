<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    protected $fillable = ['project_id', 'name', 'priority'];
    protected $casts = [
        'priority' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Task $task) {
            $task->name = trim(ucfirst($task->name));
            $task->priority = Task::where('project_id', $task->project_id)->max('priority') + 1;
        });
    }
}
