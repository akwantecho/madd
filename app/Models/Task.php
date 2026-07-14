<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'due_date' => 'date',
        'flagged' => 'boolean',
    ];

    public function exhibition()
    {
        return $this->belongsTo(Exhibition::class);
    }

    public function subtasks()
    {
        return $this->hasMany(Subtask::class)->orderBy('position')->orderBy('id');
    }

    public function assignees()
    {
        return $this->hasMany(TaskAssignee::class)->orderBy('id');
    }

    /** Keep the legacy single `assignee` column in sync with the first assignee. */
    public function syncPrimaryAssignee(): void
    {
        $this->update(['assignee' => $this->assignees()->value('name')]);
    }
}
