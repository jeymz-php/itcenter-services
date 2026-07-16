<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    protected $fillable = [
        'request_number','user_id','service_type','status',
        'paper_size','copies','purpose',
        'file_path','file_name','detected_pages','print_type','print_sides',
        'document_type','duration_minutes','computer_id',
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
    public function estimatedReceiptHeightPt(): float {
        // Fixed chrome: logos, campus header, doc title, request/status block,
        // customer block, reviewed-by block, barcode, footer note, page margins.
        $height = 320;

        // Service-detail rows vary by type
        $height += match ($this->service_type) {
            'printing'  => 6 * 12,
            'photocopy' => 3 * 12,
            'research'  => $this->computerSession ? 5 * 12 : 3 * 12,
            default     => 3 * 12,
        };

        // Purpose text wraps roughly every 40 characters at this width/font
        $purposeLines = max(1, (int) ceil(strlen((string) $this->purpose) / 40));
        $height += $purposeLines * 12;

        // Rejection note, if present, adds its own block
        if ($this->status === 'rejected' && $this->admin_note) {
            $noteLines = max(1, (int) ceil(strlen((string) $this->admin_note) / 40));
            $height += 20 + ($noteLines * 12);
        }

        return $height;
    }
}