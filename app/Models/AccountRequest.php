<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountRequest extends Model
{
    protected $fillable = [
        'user_id','type','reason','status','reviewed_by','reviewed_at','admin_note'
    ];

    protected $casts = ['reviewed_at' => 'datetime'];

    public function user()  { return $this->belongsTo(User::class); }
    public function admin() { return $this->belongsTo(Admin::class, 'reviewed_by'); }
}