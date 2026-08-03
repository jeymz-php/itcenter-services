<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    protected $fillable = [
        'user_id', 'type', 'title', 'message', 'icon', 'action_url', 'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function notify(
        User $user,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
        ?string $icon = null
    ): self {
        return static::create([
            'user_id'    => $user->id,
            'type'       => $type,
            'title'      => $title,
            'message'    => $message,
            'action_url' => $actionUrl,
            'icon'       => $icon ?? 'fa-bell',
        ]);
    }
}
