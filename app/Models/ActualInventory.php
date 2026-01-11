<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActualInventory extends Model
{
    use HasFactory;

    protected $table = 'actual_inventory';

    protected $fillable = ['product_id', 'actual_quantity', 'last_counted_at'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}