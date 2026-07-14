<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Revenue extends Model
{
    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:3',
        'revenue_date' => 'date',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
