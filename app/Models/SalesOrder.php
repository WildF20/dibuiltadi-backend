<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Sales;
use App\Models\Customer;
use App\Models\SalesOrderItem;

class SalesOrder extends Model
{
    protected $table = 'sales_orders';

    protected $fillable = ['reference_no', 'sales_id', 'customer_id'];

    public function sales()
    {
        return $this->belongsTo(Sales::class, 'sales_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(SalesOrderItem::class, 'order_id');
    }
}
