<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemMaster extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'hsn',
        'brand_code',
        'purchase_uom',
        'sales_uom',
        'conversion_factor',
        'mrp',
        'purchase_rate',
        'sales_rate',
        'cgst_percentage',
        'sgst_percentage',
        'status',
    ];
}
