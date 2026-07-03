<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'item_id',
        'no_of_package',
        'uom',
        'quantity',
        'free_qty',
        'rate',
        'discount_percent',
        'discount_amount',
        'other_discount',
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

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function item()
    {
        return $this->belongsTo(ItemMaster::class, 'item_id');
    }
}
