<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'item_id',
        'no_of_package',
        'uom',
        'quantity',
        'rate',
        'discount_amount',
        'packets',
        'mrp',
        'taxable_value',
        'cgst_rate',
        'cgst_amount',
        'sgst_rate',
        'sgst_amount',
        'tax_amount',
        'amount',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function item()
    {
        return $this->belongsTo(ItemMaster::class, 'item_id');
    }
}
