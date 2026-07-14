<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractItem extends Model
{
    protected $guarded = [];

    protected $casts = [
        'qty' => 'decimal:2',
        'price' => 'decimal:2',
        'days' => 'integer',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
}
