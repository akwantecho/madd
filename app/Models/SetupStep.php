<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SetupStep extends Model
{
    protected $guarded = [];

    protected $casts = [
        'step_date' => 'date',
    ];

    public function exhibition()
    {
        return $this->belongsTo(Exhibition::class);
    }
}
