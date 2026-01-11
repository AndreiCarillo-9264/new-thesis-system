<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Distribution extends Model
{
    use HasFactory;

    protected $fillable = ['job_order_id', 'product_id', 'quantity_distributed', 'distribution_date', 'destination'];

    public function jobOrder()
    {
        return $this->belongsTo(JobOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // For business rule
    public function getTotalDistributedAttribute()
    {
        return $this->jobOrder->distributions->sum('quantity_distributed');
    }
}