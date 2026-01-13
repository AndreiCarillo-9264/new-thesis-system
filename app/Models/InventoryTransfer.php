<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransfer extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'quantity', 'from_location', 'to_location', 'transfer_date'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}