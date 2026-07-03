<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockLedger extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'transaction_type',
        'transaction_id',
        'quantity',
        'running_balance',
    ];

    public function item()
    {
        return $this->belongsTo(ItemMaster::class, 'item_id');
    }
}
