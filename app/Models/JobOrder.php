<?php
// Updated: app/Models/JobOrder.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'jo_number', 'customer_name', 'product_id', 'ordered_quantity', 'unit_price',
        'jo_date', 'due_date', 'status', 'user_id', 'priority', 'notes'
    ];

    protected $appends = ['total_produced', 'production_status', 'total_amount'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function salesRep()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function finishedGoods()
    {
        return $this->hasMany(FinishedGood::class);
    }

    public function distributions()
    {
        return $this->hasMany(Distribution::class);
    }

    
    public function getTotalProducedAttribute()
    {
        return $this->finishedGoods->sum('quantity_produced');
    }

    public function getProductionStatusAttribute()
    {
        $produced = $this->total_produced;
        if ($produced >= $this->ordered_quantity) {
            return 'completed';
        }
        if ($produced > 0) {
            return 'in_production';
        }
        return 'pending';
    }

    public function getTotalAmountAttribute()
    {
        return $this->ordered_quantity * $this->unit_price;
    }
}