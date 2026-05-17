<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuestRequest extends Model
{
    protected $fillable = [
        'request_number','role','first_name','last_name','email',
        'id_number','campus','service_type','status',
        'paper_size','copies','file_path','file_name',
        'print_type','purpose','duration_minutes',
        'computer_id','reviewed_by','reviewed_at','admin_note',
    ];

    protected $casts = ['reviewed_at' => 'datetime'];

    public function getFullNameAttribute(): string {
        return "{$this->first_name} {$this->last_name}";
    }

    public function reviewer() {
        return $this->belongsTo(Admin::class, 'reviewed_by');
    }

    public function computer() {
        return $this->belongsTo(Computer::class);
    }

    public function computerSession() {
        return $this->hasOne(GuestComputerSession::class);
    }

    public static function generateNumber(): string {
        $last = static::orderByDesc('id')->first();
        $next = $last ? ($last->id + 1) : 1;
        return 'G-' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }

    public function getServiceIconAttribute(): string {
        return match($this->service_type) {
            'printing'  => 'fa-print',
            'photocopy' => 'fa-copy',
            'research'  => 'fa-desktop',
            default     => 'fa-file',
        };
    }

    public function getServiceColorAttribute(): string {
        return match($this->service_type) {
            'printing'  => 'var(--blue)',
            'photocopy' => 'var(--orange)',
            'research'  => 'var(--g600)',
            default     => 'var(--gray600)',
        };
    }
}