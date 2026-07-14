<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $guarded = [];

    protected $casts = [
        'doc_date' => 'date',
    ];

    public function exhibition()
    {
        return $this->belongsTo(Exhibition::class);
    }
}
