<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    protected $fillable = [
        'admin_id', 'email', 'campus', 'password', 'role',
    ];

    protected $hidden = ['password'];

    // Remove the $casts with 'hashed' — not supported in your Laravel version
    // Hashing is done manually via Hash::make() in seeders/controllers
}