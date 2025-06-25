<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\SalesArea;

class Sales extends Model
{
    protected $table = 'sales';

    protected $fillable = ['user_id', 'area_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function area()
    {
        return $this->belongsTo(SalesArea::class, 'area_id');
    }
}
