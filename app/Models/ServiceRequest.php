<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    const DEFAULT_DAILY_PRINTING_LIMIT  = 10;
    const DEFAULT_DAILY_PHOTOCOPY_LIMIT = 10;
    const DEFAULT_DAILY_RESEARCH_LIMIT  = 60;

    protected $fillable = [
        'request_number','user_id','service_type','status',
        'paper_size','copies','purpose',
        'file_path','file_name','detected_pages','print_type','print_sides',
        'document_type','duration_minutes','computer_id',
        'reviewed_by','reviewed_at','admin_note','total_price',
        'rating_prompted_at','rating_dismissed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'rating_prompted_at' => 'datetime',
        'rating_dismissed_at' => 'datetime',
    ];

    public function user()  { return $this->belongsTo(User::class); }
    public function admin() { return $this->belongsTo(Admin::class, 'reviewed_by'); }
    public function rating() { return $this->hasOne(Rating::class); }

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

    public static function dailyPrintingLimit(): int {
        return (int) Setting::get('daily_printing_page_limit', self::DEFAULT_DAILY_PRINTING_LIMIT);
    }

    public static function dailyPhotocopyLimit(): int {
        return (int) Setting::get('daily_photocopy_page_limit', self::DEFAULT_DAILY_PHOTOCOPY_LIMIT);
    }

    public static function dailyResearchLimit(): int {
        return (int) Setting::get('daily_research_minutes', self::DEFAULT_DAILY_RESEARCH_LIMIT);
    }

    public function printingSheetCount(): int {
        return ((int) ($this->detected_pages ?: 1)) * (int) ($this->copies ?: 1);
    }

    public static function printingPagesUsedToday(int $userId, ?int $excludeRequestId = null): int {
        $query = static::where('user_id', $userId)
            ->where('service_type', 'printing')
            ->whereNotIn('status', ['rejected', 'cancelled'])
            ->where('created_at', '>=', now()->startOfDay());

        if ($excludeRequestId !== null) {
            $query->where('id', '!=', $excludeRequestId);
        }

        return (int) $query->get(['copies', 'detected_pages'])
            ->sum(fn ($r) => ((int) ($r->detected_pages ?: 1)) * (int) $r->copies);
    }

    public static function printingPagesRemainingToday(int $userId, ?int $excludeRequestId = null): int {
        return max(0, self::dailyPrintingLimit() - self::printingPagesUsedToday($userId, $excludeRequestId));
    }

    public static function photocopyPagesUsedToday(int $userId, ?int $excludeRequestId = null): int {
        $query = static::where('user_id', $userId)
            ->where('service_type', 'photocopy')
            ->whereNotIn('status', ['rejected', 'cancelled'])
            ->where('created_at', '>=', now()->startOfDay());

        if ($excludeRequestId !== null) {
            $query->where('id', '!=', $excludeRequestId);
        }

        return (int) $query->sum('copies');
    }

    public static function photocopyPagesRemainingToday(int $userId, ?int $excludeRequestId = null): int {
        return max(0, self::dailyPhotocopyLimit() - self::photocopyPagesUsedToday($userId, $excludeRequestId));
    }

    public static function minutesUsedToday(int $userId): int {
        // Count both the originally requested duration and every minute added
        // through the active/completed computer session extension workflow.
        // Loading the one-to-one session keeps this database-driver agnostic.
        return (int) static::with(['computerSession:id,service_request_id,extended_minutes'])
            ->where('user_id', $userId)
            ->where('service_type', 'research')
            ->whereNotIn('status', ['rejected', 'cancelled'])
            ->where('created_at', '>=', now()->startOfDay())
            ->get(['id', 'duration_minutes'])
            ->sum(function ($serviceRequest) {
                $baseMinutes = (int) ($serviceRequest->duration_minutes ?? 0);
                $extendedMinutes = (int) ($serviceRequest->computerSession?->extended_minutes ?? 0);

                return $baseMinutes + $extendedMinutes;
            });
    }

    public static function minutesRemainingToday(int $userId): int {
        return max(0, self::dailyResearchLimit() - self::minutesUsedToday($userId));
    }

    public function estimatedReceiptHeightPt(): float {
        $height = 320;

        $height += match ($this->service_type) {
            'printing'  => 6 * 12,
            'photocopy' => 3 * 12,
            'research'  => $this->computerSession ? 5 * 12 : 3 * 12,
            default     => 3 * 12,
        };

        $purposeLines = max(1, (int) ceil(strlen((string) $this->purpose) / 40));
        $height += $purposeLines * 12;

        if ($this->status === 'rejected' && $this->admin_note) {
            $noteLines = max(1, (int) ceil(strlen((string) $this->admin_note) / 40));
            $height += 20 + ($noteLines * 12);
        }

        return $height;
    }
}