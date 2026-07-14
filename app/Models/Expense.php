<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:3',
        'expense_date' => 'date',
    ];

    public function exhibition()
    {
        return $this->belongsTo(Exhibition::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
