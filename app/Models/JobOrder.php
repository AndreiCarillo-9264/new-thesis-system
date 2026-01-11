<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobOrder extends Model
{
    use HasFactory;

    protected $fillable = ['jo_number', 'product_id', 'ordered_quantity', 'jo_date', 'status'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function finishedGoods()
    {
        return $this->hasMany(FinishedGood::class);
    }

    public function distributions()
    {
        return $this->hasMany(Distribution::class);
    }

    // Business rule enforcement (in controller or observer)
    public function getTotalProducedAttribute()
    {
        return $this->finishedGoods->sum('quantity_produced');
    }
}