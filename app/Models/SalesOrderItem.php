<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use App\Models\SalesOrder;

class SalesOrderItem extends Model
{
    protected $table = 'sales_order_items';

    protected $fillable = ['quantity', 'production_price', 'selling_price', 'product_id', 'order_id'];

    public $timestamps = false;

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function order()
    {
        return $this->belongsTo(SalesOrder::class, 'order_id');
    }
}
