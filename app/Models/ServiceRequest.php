<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    protected $fillable = [
        'request_number','user_id','service_type','status',
        'paper_size','copies','purpose',
        'file_path','file_name','print_type','print_sides',
        'document_type','duration_minutes',
        'reviewed_by','reviewed_at','admin_note','total_price',
    ];

    protected $casts = ['reviewed_at' => 'datetime'];

    public function user()  { return $this->belongsTo(User::class); }
    public function admin() { return $this->belongsTo(Admin::class, 'reviewed_by'); }

    public static function generateNumber(): string {
        $last = static::orderByDesc('id')->first();
        $next = $last ? ($last->id + 1) : 1;
        return '#' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }

    public function getServiceColorAttribute(): string {
        return match($this->service_type) {
            'printing'  => 'var(--blue)',
            'photocopy' => 'var(--orange)',
            'research'  => 'var(--g600)',
            default     => 'var(--gray600)',
        };
    }

    public function getServiceIconAttribute(): string {
        return match($this->service_type) {
            'printing'  => 'fa-print',
            'photocopy' => 'fa-copy',
            'research'  => 'fa-desktop',
            default     => 'fa-file',
        };
    }
    public function computer() {
        return $this->belongsTo(Computer::class);
    }
    public function computerSession() {
        return $this->hasOne(ComputerSession::class);
    }
}