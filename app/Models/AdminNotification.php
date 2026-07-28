<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminNotification extends Model
{
    protected $fillable = [
        'type','title','message','notifiable_id','notifiable_type',
        'is_read','action_url','icon','campus'
    ];

    public function notifiable() { return $this->morphTo(); }

    public static function notify(string $type, string $title, string $message, $notifiable, string $actionUrl = null, string $icon = null): void {
        static::create([
            'type'            => $type,
            'title'           => $title,
            'message'         => $message,
            'notifiable_id'   => $notifiable->id,
            'notifiable_type' => get_class($notifiable),
            'action_url'      => $actionUrl,
            'icon'            => $icon ?? 'fa-bell',
            // Every model this is called with (User, GuestRequest, Admin) has
            // its own `campus` attribute — captured automatically here so no
            // existing call site (~20 of them across the app) needs to change.
            // Null means campus-agnostic (visible to every admin).
            'campus'          => $notifiable->campus ?? null,
        ]);
    }
}