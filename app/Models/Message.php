<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'user_id','service_request_id','sender_type','sender_admin_id',
        'body','is_read_by_user','is_read_by_admin',
    ];

    protected $casts = [
        'is_read_by_user'  => 'boolean',
        'is_read_by_admin' => 'boolean',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function serviceRequest() { return $this->belongsTo(ServiceRequest::class); }
    public function senderAdmin() { return $this->belongsTo(Admin::class, 'sender_admin_id'); }

    public function scopeUnreadByAdmin($q) {
        return $q->where('sender_type', 'user')->where('is_read_by_admin', false);
    }

    public function scopeUnreadByUser($q) {
        return $q->where('sender_type', 'admin')->where('is_read_by_user', false);
    }

    public function getSenderNameAttribute(): string {
        if ($this->sender_type === 'admin') {
            return $this->senderAdmin?->admin_id ?? 'IT Center Admin';
        }
        return $this->user?->full_name ?? 'You';
    }
}