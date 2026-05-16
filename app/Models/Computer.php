<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Computer extends Model
{
    protected $fillable = [
        'name','specs','status','deactivation_note','sort_order'
    ];

    public function sessions() {
        return $this->hasMany(ComputerSession::class);
    }

    public function activeSession() {
        return $this->hasOne(ComputerSession::class)
                    ->whereIn('status',['active','extended']);
    }

    public function isAvailable(): bool {
        return $this->status === 'available';
    }
}