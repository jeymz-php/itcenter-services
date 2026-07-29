<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    protected $fillable = [
        'service_request_id','user_id','is_anonymous','stars','comment','suggestions',
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

    // Masked to "Jam***" — first 3 letters visible, everything after
    // replaced with asterisks. Last name is never shown at all when
    // anonymous, since revealing it alongside a partial first name would
    // largely defeat the point of choosing anonymity.
    public function getDisplayFirstNameAttribute(): string {
        if (!$this->is_anonymous) {
            return $this->user->first_name;
        }
        $name = $this->user->first_name;
        if (strlen($name) <= 3) {
            return $name;
        }
        return substr($name, 0, 3) . str_repeat('*', strlen($name) - 3);
    }

    public function getDisplayLastNameAttribute(): ?string {
        return $this->is_anonymous ? null : $this->user->last_name;
    }

    // Masked to "****0977" — only the last 4 digits visible.
    public function getDisplayIdNumberAttribute(): string {
        $id = $this->user->id_number;
        if (!$this->is_anonymous) {
            return $id;
        }
        if (strlen($id) <= 4) {
            return $id;
        }
        return str_repeat('*', strlen($id) - 4) . substr($id, -4);
    }

    // Campus is always shown in full, anonymous or not.
    public function getDisplayCampusAttribute(): string {
        return config('campuses.' . $this->user->campus, $this->user->campus);
    }
}