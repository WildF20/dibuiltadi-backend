<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Sales;

class SalesTarget extends Model
{
    protected $table = 'sales_targets';

    protected $fillable = ['active_date', 'amount', 'sales_id'];

    public function sales()
    {
        return $this->belongsTo(Sales::class, 'sales_id');
    }
}
