<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $guarded = [];

    protected $casts = [
        'book_balance' => 'decimal:3',
        'statement_balance' => 'decimal:3',
    ];

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function revenues()
    {
        return $this->hasMany(Revenue::class);
    }

    public function getDifferenceAttribute(): float
    {
        return (float) $this->book_balance - (float) $this->statement_balance;
    }

    public function getBalancedAttribute(): bool
    {
        return abs($this->difference) < 0.0001;
    }
}
