<?php
// New: app/Exports/SalesExport.php
namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SalesExport implements FromCollection, WithHeadings
{
    protected $data;
    protected $type;

    public function __construct($data, $type)
    {
        $this->data = $data;
        $this->type = $type;
    }

    public function collection()
    {
        if ($this->type === 'Detailed Report') {
            return $this->data->map(function ($order) {
                return [
                    'Order ID' => $order->jo_number,
                    'Customer' => $order->customer_name,
                    'Product' => $order->product?->product_name,
                    'Quantity' => $order->ordered_quantity,
                    'Total Amount' => $order->total_amount,
                    'Order Status' => $order->status,
                    'Production Status' => $order->production_status,
                    'Due Date' => $order->due_date,
                    'Sales Rep' => $order->salesRep?->name,
                ];
            });
        } elseif ($this->type === 'Sales Summary') {
            return collect($this->data)->map(function ($value, $key) {
                return ['Metric' => ucfirst(str_replace('_', ' ', $key)), 'Value' => $value];
            });
        } elseif ($this->type === 'Customer Report') {
            return $this->data->map(function ($item) {
                return [
                    'Customer' => $item->customer_name,
                    'Orders Count' => $item->orders_count,
                    'Total Spent' => $item->total_spent,
                ];
            });
        }

        return new Collection();
    }

    public function headings(): array
    {
        if ($this->type === 'Detailed Report') {
            return ['Order ID', 'Customer', 'Product', 'Quantity', 'Total Amount', 'Order Status', 'Production Status', 'Due Date', 'Sales Rep'];
        } elseif ($this->type === 'Sales Summary') {
            return ['Metric', 'Value'];
        } elseif ($this->type === 'Customer Report') {
            return ['Customer', 'Orders Count', 'Total Spent'];
        }

        return [];
    }
}