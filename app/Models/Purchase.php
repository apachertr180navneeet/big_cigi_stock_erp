<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vendor_id',
        'bill_no',
        'bill_date',
        'eway_bill_no',
        'supplier_invoice_no',
        'supplier_invoice_date',
        'other_references',
        'discount_allowed',
        'cgst_total',
        'sgst_total',
        'total_amount',
        'status',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
