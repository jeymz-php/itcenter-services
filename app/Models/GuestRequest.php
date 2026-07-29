<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuestRequest extends Model
{
    protected $fillable = [
        'request_number','role','first_name','last_name','email',
        'id_number','campus','service_type','status',
        'paper_size','copies','file_path','file_name','detected_pages',
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

    // ── DAILY LIMITS — GUESTS ──
    public static function dailyPrintingLimit(): int {
        return ServiceRequest::dailyPrintingLimit();
    }

    public static function dailyPhotocopyLimit(): int {
        return ServiceRequest::dailyPhotocopyLimit();
    }

    public static function dailyResearchLimit(): int {
        return ServiceRequest::dailyResearchLimit();
    }

    public static function printingPagesUsedToday(string $email): int {
        return (int) static::where('email', $email)
            ->where('service_type', 'printing')
            ->whereNotIn('status', ['rejected', 'cancelled'])
            ->where('created_at', '>=', now()->startOfDay())
            ->get(['copies', 'detected_pages'])
            ->sum(fn ($r) => ((int) ($r->detected_pages ?: 1)) * (int) $r->copies);
    }

    public static function printingPagesRemainingToday(string $email): int {
        return max(0, self::dailyPrintingLimit() - self::printingPagesUsedToday($email));
    }

    public static function photocopyPagesUsedToday(string $email): int {
        return (int) static::where('email', $email)
            ->where('service_type', 'photocopy')
            ->whereNotIn('status', ['rejected', 'cancelled'])
            ->where('created_at', '>=', now()->startOfDay())
            ->sum('copies');
    }

    public static function photocopyPagesRemainingToday(string $email): int {
        return max(0, self::dailyPhotocopyLimit() - self::photocopyPagesUsedToday($email));
    }

    public static function minutesUsedToday(string $email): int {
        return (int) static::where('email', $email)
            ->where('service_type', 'research')
            ->whereNotIn('status', ['rejected', 'cancelled'])
            ->where('created_at', '>=', now()->startOfDay())
            ->sum('duration_minutes');
    }

    public static function minutesRemainingToday(string $email): int {
        return max(0, self::dailyResearchLimit() - self::minutesUsedToday($email));
    }
}