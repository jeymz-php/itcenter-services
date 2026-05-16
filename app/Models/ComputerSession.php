<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComputerSession extends Model
{
    protected $fillable = [
        'service_request_id','computer_id','user_id',
        'duration_minutes','extended_minutes',
        'started_at','ends_at','ended_at','status',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ends_at'    => 'datetime',
        'ended_at'   => 'datetime',
    ];

    public function computer()       { return $this->belongsTo(Computer::class); }
    public function user()           { return $this->belongsTo(User::class); }
    public function serviceRequest() { return $this->belongsTo(ServiceRequest::class); }

    public function getRemainingSecondsAttribute(): int {
        if (!$this->ends_at) return 0;
        return max(0, now()->diffInSeconds($this->ends_at, false));
    }

    public function getTotalMinutesAttribute(): int {
        return $this->duration_minutes + $this->extended_minutes;
    }
}