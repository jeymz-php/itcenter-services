<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    protected $fillable = [
        'service_request_id','user_id','guest_request_id',
        'is_anonymous','stars','comment','suggestions',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'stars'        => 'integer',
    ];

    public function serviceRequest() {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function guestRequest() {
        return $this->belongsTo(GuestRequest::class);
    }

    // Pulls from whichever source this rating actually belongs to — a
    // logged-in student's User record, or a guest's GuestRequest record
    // (which already stores name/id/campus directly, no join needed).
    private function sourceFirstName(): ?string {
        return $this->guest_request_id ? $this->guestRequest?->first_name : $this->user?->first_name;
    }
    private function sourceLastName(): ?string {
        return $this->guest_request_id ? $this->guestRequest?->last_name : $this->user?->last_name;
    }
    private function sourceIdNumber(): ?string {
        return $this->guest_request_id ? $this->guestRequest?->id_number : $this->user?->id_number;
    }
    private function sourceCampus(): ?string {
        return $this->guest_request_id ? $this->guestRequest?->campus : $this->user?->campus;
    }

    public function getDisplayFirstNameAttribute(): string {
        $name = $this->sourceFirstName() ?? '';
        if (!$this->is_anonymous) {
            return $name;
        }
        if (strlen($name) <= 3) {
            return $name;
        }
        return substr($name, 0, 3) . str_repeat('*', strlen($name) - 3);
    }

    public function getDisplayLastNameAttribute(): ?string {
        return $this->is_anonymous ? null : $this->sourceLastName();
    }

    public function getDisplayIdNumberAttribute(): string {
        $id = $this->sourceIdNumber() ?? '';
        if (!$this->is_anonymous) {
            return $id;
        }
        if (strlen($id) <= 4) {
            return $id;
        }
        return str_repeat('*', strlen($id) - 4) . substr($id, -4);
    }

    public function getDisplayCampusAttribute(): string {
        $campus = $this->sourceCampus();
        return config('campuses.' . $campus, $campus);
    }

    // Convenience accessors so the admin view doesn't need to branch on
    // serviceRequest-vs-guestRequest everywhere it displays these.
    public function getRequestNumberAttribute(): ?string {
        return $this->guest_request_id ? $this->guestRequest?->request_number : $this->serviceRequest?->request_number;
    }

    public function getServiceTypeValueAttribute(): ?string {
        return $this->guest_request_id ? $this->guestRequest?->service_type : $this->serviceRequest?->service_type;
    }
}