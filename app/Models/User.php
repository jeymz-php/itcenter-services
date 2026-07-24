<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = [
        'id_number','first_name','last_name','email',
        'profile_picture','campus','user_type','password','status',
        'research_restricted','research_restriction_note',
    ];
    protected $hidden = ['password'];
    protected $casts = ['research_restricted' => 'boolean'];

    public function getFullNameAttribute(): string {
        return "{$this->first_name} {$this->last_name}";
    }
    public function isVerified(): bool   { return $this->status === 'active'; }
    public function isPending(): bool    { return $this->status === 'pending'; }
    public function isDeactivated(): bool{ return $this->status === 'deactivated'; }
    public function isArchived(): bool   { return $this->status === 'archived'; }

    public function accountRequests() { return $this->hasMany(AccountRequest::class); }
    public function pendingRequest(string $type) {
        return $this->accountRequests()->where('type',$type)->where('status','pending')->first();
    }
    public function serviceRequests() {
        return $this->hasMany(\App\Models\ServiceRequest::class)->latest();
    }
}