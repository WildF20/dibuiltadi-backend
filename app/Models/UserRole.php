<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class UserRole extends Model
{
    protected $table = 'user_roles';

    protected $fillable = ['name'];

    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }
}
