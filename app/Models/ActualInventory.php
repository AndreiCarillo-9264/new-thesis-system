<?php
// Updated: app/Models/ActualInventory.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActualInventory extends Model
{
    use HasFactory;

    protected $table = 'actual_inventory';

    protected $fillable = [
        'product_id', 'actual_quantity', 'last_counted_at',
        'min_stock', 'max_stock', 'location', 'supplier', 'unit_cost'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getStatusAttribute()
    {
        if ($this->actual_quantity < $this->min_stock) {
            return 'low';
        } elseif ($this->actual_quantity > $this->max_stock) {
            return 'overstocked';
        }
        return 'adequate';
    }

    public function getTotalValueAttribute()
    {
        return $this->actual_quantity * $this->unit_cost;
    }
}