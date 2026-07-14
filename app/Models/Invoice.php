<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'vat_rate' => 'decimal:2',
        'discount' => 'decimal:2',
        'paid' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(Contact::class, 'client_id');
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function exhibition()
    {
        return $this->belongsTo(Exhibition::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('position');
    }

    public function getSubtotalAttribute(): float
    {
        return (float) $this->items->sum(fn ($i) => $i->qty * $i->price);
    }

    public function getVatAttribute(): float
    {
        $taxable = max(0, $this->subtotal - (float) $this->discount);

        return $taxable * (float) $this->vat_rate / 100;
    }

    /** Grand total, VAT included, after discount. */
    public function getTotalAttribute(): float
    {
        $taxable = max(0, $this->subtotal - (float) $this->discount);

        return $taxable + $this->vat;
    }

    public function getAmountDueAttribute(): float
    {
        return $this->total - (float) $this->paid;
    }
}
