<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['product_code', 'product_name', 'category', 'unit', 'is_active'];

    public function jobOrders()
    {
        return $this->hasMany(JobOrder::class);
    }

    public function finishedGoods()
    {
        return $this->hasMany(FinishedGood::class);
    }

    public function distributions()
    {
        return $this->hasMany(Distribution::class);
    }

    public function actualInventory()
    {
        return $this->hasOne(ActualInventory::class);
    }

    public function inventoryTransfers()
    {
        return $this->hasMany(InventoryTransfer::class);
    }
}