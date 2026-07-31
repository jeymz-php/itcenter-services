<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    private const SERVICE_SETTING_KEYS = [
        'printing'  => 'service_printing_enabled',
        'photocopy' => 'service_photocopy_enabled',
        'research'  => 'service_research_enabled',
    ];

    private const SERVICE_LABELS = [
        'printing'  => 'Printing',
        'photocopy' => 'Photocopy',
        'research'  => 'Research / PC Lab',
    ];

    public static function get(string $key, $default = null) {
        return static::where('key', $key)->value('value') ?? $default;
    }

    public static function set(string $key, $value): void {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public static function isServiceAvailable(string $service): bool {
        $key = self::SERVICE_SETTING_KEYS[$service] ?? null;
        return $key ? static::get($key, '1') === '1' : false;
    }

    public static function setServiceAvailability(string $service, bool $available): void {
        $key = self::SERVICE_SETTING_KEYS[$service] ?? null;
        if (!$key) {
            throw new \InvalidArgumentException("Unknown service: {$service}");
        }
        static::set($key, $available ? '1' : '0');
    }

    public static function serviceAvailability(): array {
        return collect(array_keys(self::SERVICE_SETTING_KEYS))
            ->mapWithKeys(fn ($service) => [$service => static::isServiceAvailable($service)])
            ->all();
    }

    public static function serviceLabel(string $service): string {
        return self::SERVICE_LABELS[$service] ?? ucfirst($service);
    }

    public static function serviceUnavailableMessage(string $service): string {
        return static::serviceLabel($service) . ' service is currently unavailable. Please use the User Manual or Infographics for guidance and check again later, or contact the IT Center Services Desk.';
    }

    /**
     * Version history is stored as JSON inside the existing settings table,
     * keeping this feature compatible without a new database migration.
     */
    public static function versionHistory(): array {
        $decoded = json_decode((string) static::get('system_version_history', '[]'), true);
        if (!is_array($decoded)) {
            return [];
        }

        return collect($decoded)
            ->filter(fn ($entry) => is_array($entry) && !empty($entry['version']))
            ->sortByDesc(fn ($entry) => $entry['released_at'] ?? '')
            ->values()
            ->all();
    }

    public static function saveVersionLog(string $version, string $notes, ?string $releasedAt = null, ?string $updatedBy = null): void {
        $history = collect(static::versionHistory());
        $existing = $history->firstWhere('version', $version);

        $entry = [
            'version'     => $version,
            'notes'       => $notes,
            'released_at' => $existing['released_at'] ?? ($releasedAt ?: now()->toIso8601String()),
            'updated_at'  => now()->toIso8601String(),
            'updated_by'  => $existing['updated_by'] ?? $updatedBy,
        ];

        $history = $history
            ->reject(fn ($item) => ($item['version'] ?? null) === $version)
            ->prepend($entry)
            ->sortByDesc(fn ($item) => $item['released_at'] ?? '')
            ->take(20)
            ->values();

        static::set('system_version_history', $history->toJson());
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
