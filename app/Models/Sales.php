<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\User;
use App\Models\SalesArea;
use Illuminate\Database\Eloquent\Model;

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

    public function target()
    {
        return $this->hasMany(SalesTarget::class, 'sales_id');
    }

    public function latestTarget()
    {
        return $this->hasOne(SalesTarget::class, 'sales_id')->latest('active_date');
    }

    public function getTargetAmount()
    {
        $latestTarget = $this->latestTarget;
        
        if (!$latestTarget) {
            return 0;
        }
        
        if (Carbon::parse($latestTarget->active_date)->gte(Carbon::today())) {
            return $latestTarget->amount;
        }
        
        return 0;
    }
}
