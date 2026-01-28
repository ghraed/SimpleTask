<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = ['name'];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class)->orderBy('priority');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Project $project) {
            $project->name = trim(ucfirst($project->name));
        });
    }
}
