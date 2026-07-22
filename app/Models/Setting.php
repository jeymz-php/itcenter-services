<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null) {
        return static::where('key', $key)->value('value') ?? $default;
    }

    public static function set(string $key, $value): void {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    // Whether right now falls within the configured IT Center operating hours
    // (defaults to 7:00 AM – 9:30 PM). Used to block new service-request
    // submissions outside business hours — students/faculty can still log in
    // and browse, they just can't submit while the IT Center is closed.
    public static function isWithinSystemHours(): bool {
        $open  = static::get('system_open_time', '07:00');
        $close = static::get('system_close_time', '21:30');
        $now   = now()->format('H:i');
        return $now >= $open && $now <= $close;
    }

    public static function systemHoursLabel(): string {
        $open  = static::get('system_open_time', '07:00');
        $close = static::get('system_close_time', '21:30');
        return \Carbon\Carbon::createFromFormat('H:i', $open)->format('g:i A')
            . ' – ' . \Carbon\Carbon::createFromFormat('H:i', $close)->format('g:i A');
    }
}