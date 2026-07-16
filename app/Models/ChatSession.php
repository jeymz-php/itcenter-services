<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatSession extends Model
{
    protected $fillable = ['user_id', 'opened_at', 'closed_at', 'closed_by', 'closed_by_admin_id'];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function closedByAdmin() { return $this->belongsTo(Admin::class, 'closed_by_admin_id'); }
    public function messages() { return $this->hasMany(Message::class); }

    // The currently open session for a user, if any (does NOT create one)
    public static function openFor(int $userId): ?self {
        return static::where('user_id', $userId)->whereNull('closed_at')->latest('id')->first();
    }

    // Get the open session, creating a fresh one if none exists (used when sending)
    public static function getOrOpenFor(int $userId): self {
        return static::openFor($userId) ?? static::create(['user_id' => $userId, 'opened_at' => now()]);
    }

    // Whether this user has ever had a conversation that is now closed
    // (used to distinguish "brand new" vs "conversation was ended" empty states)
    public static function hasEndedSessionFor(int $userId): bool {
        return static::where('user_id', $userId)->whereNotNull('closed_at')->exists();
    }
}