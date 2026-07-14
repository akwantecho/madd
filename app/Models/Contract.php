<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'vat_rate' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(Contact::class, 'client_id');
    }

    public function exhibition()
    {
        return $this->belongsTo(Exhibition::class);
    }

    public function items()
    {
        return $this->hasMany(ContractItem::class)->orderBy('position');
    }

    public function schedules()
    {
        return $this->hasMany(PaymentSchedule::class)->orderBy('position');
    }

    public function terms()
    {
        return $this->hasMany(ContractTerm::class)->orderBy('position');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /** Net of items, before VAT. */
    public function getSubtotalAttribute(): float
    {
        return (float) $this->items->sum(fn ($i) => $i->qty * $i->price * max(1, (int) $i->days));
    }

    public function getVatAttribute(): float
    {
        return $this->subtotal * (float) $this->vat_rate / 100;
    }

    /** Total contract value, VAT included. */
    public function getValueAttribute(): float
    {
        return $this->subtotal + $this->vat;
    }
}
