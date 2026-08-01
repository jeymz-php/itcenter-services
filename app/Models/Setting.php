<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    private static array $runtimeCache = [];

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

    private const WEEK_DAYS = [
        'monday', 'tuesday', 'wednesday', 'thursday',
        'friday', 'saturday', 'sunday',
    ];

    public static function get(string $key, $default = null)
    {
        if (array_key_exists($key, self::$runtimeCache)) {
            return self::$runtimeCache[$key];
        }

        $value = static::where('key', $key)->value('value');
        self::$runtimeCache[$key] = $value ?? $default;
        return self::$runtimeCache[$key];
    }

    public static function set(string $key, $value): void
    {
        $value = is_bool($value) ? ($value ? '1' : '0') : (string) $value;
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        self::$runtimeCache[$key] = $value;
    }

    public static function isServiceAvailable(string $service): bool
    {
        $key = self::SERVICE_SETTING_KEYS[$service] ?? null;
        return $key ? static::get($key, '1') === '1' : false;
    }

    public static function setServiceAvailability(string $service, bool $available): void
    {
        $key = self::SERVICE_SETTING_KEYS[$service] ?? null;
        if (!$key) {
            throw new \InvalidArgumentException("Unknown service: {$service}");
        }
        static::set($key, $available ? '1' : '0');
    }

    public static function serviceAvailability(): array
    {
        $missingKeys = collect(self::SERVICE_SETTING_KEYS)
            ->values()
            ->filter(fn ($key) => !array_key_exists($key, self::$runtimeCache))
            ->values();

        if ($missingKeys->isNotEmpty()) {
            $stored = static::whereIn('key', $missingKeys)->pluck('value', 'key');
            foreach ($missingKeys as $key) {
                self::$runtimeCache[$key] = $stored[$key] ?? '1';
            }
        }

        return collect(self::SERVICE_SETTING_KEYS)
            ->mapWithKeys(fn ($key, $service) => [
                $service => (self::$runtimeCache[$key] ?? '1') === '1',
            ])
            ->all();
    }

    public static function serviceLabel(string $service): string
    {
        return self::SERVICE_LABELS[$service] ?? ucfirst($service);
    }

    public static function serviceUnavailableMessage(string $service): string
    {
        return static::serviceLabel($service) . ' service is currently unavailable. Please use the User Manual or Infographics for guidance and check again later, or contact the IT Center Services Desk.';
    }

    /**
     * Default IT Center schedule requested by the institution:
     * Monday-Thursday and Saturday, 7:00 AM-6:30 PM.
     * Friday and Sunday are closed until manually enabled by a Super Admin.
     */
    public static function defaultOperatingSchedule(): array
    {
        return [
            'monday'    => ['enabled' => true,  'open' => '07:00', 'close' => '18:30'],
            'tuesday'   => ['enabled' => true,  'open' => '07:00', 'close' => '18:30'],
            'wednesday' => ['enabled' => true,  'open' => '07:00', 'close' => '18:30'],
            'thursday'  => ['enabled' => true,  'open' => '07:00', 'close' => '18:30'],
            'friday'    => ['enabled' => false, 'open' => '07:00', 'close' => '18:30'],
            'saturday'  => ['enabled' => true,  'open' => '07:00', 'close' => '18:30'],
            'sunday'    => ['enabled' => false, 'open' => '07:00', 'close' => '18:30'],
        ];
    }

    public static function operatingSchedule(): array
    {
        $defaults = static::defaultOperatingSchedule();
        $decoded = json_decode((string) static::get('operating_schedule', '[]'), true);
        if (!is_array($decoded)) {
            $decoded = [];
        }

        $schedule = [];
        foreach (self::WEEK_DAYS as $day) {
            $entry = is_array($decoded[$day] ?? null) ? $decoded[$day] : [];
            $open = preg_match('/^\d{2}:\d{2}$/', (string) ($entry['open'] ?? ''))
                ? $entry['open'] : $defaults[$day]['open'];
            $close = preg_match('/^\d{2}:\d{2}$/', (string) ($entry['close'] ?? ''))
                ? $entry['close'] : $defaults[$day]['close'];

            $schedule[$day] = [
                'enabled' => array_key_exists('enabled', $entry)
                    ? filter_var($entry['enabled'], FILTER_VALIDATE_BOOLEAN)
                    : $defaults[$day]['enabled'],
                'open'  => $open,
                'close' => $close,
            ];
        }

        return $schedule;
    }

    public static function setOperatingSchedule(array $schedule): void
    {
        $defaults = static::defaultOperatingSchedule();
        $normalized = [];

        foreach (self::WEEK_DAYS as $day) {
            $entry = is_array($schedule[$day] ?? null) ? $schedule[$day] : [];
            $normalized[$day] = [
                'enabled' => (bool) ($entry['enabled'] ?? false),
                'open'    => (string) ($entry['open'] ?? $defaults[$day]['open']),
                'close'   => (string) ($entry['close'] ?? $defaults[$day]['close']),
            ];
        }

        static::set('operating_schedule', json_encode($normalized, JSON_UNESCAPED_SLASHES));

        // Keep legacy keys synchronized for any older client that still reads them.
        static::set('system_open_time', $normalized['monday']['open']);
        static::set('system_close_time', $normalized['monday']['close']);
    }

    public static function todaySchedule(?Carbon $at = null): array
    {
        $at = ($at ?: now())->copy()->timezone(config('app.timezone', 'Asia/Manila'));
        $day = strtolower($at->format('l'));
        $entry = static::operatingSchedule()[$day] ?? ['enabled' => false, 'open' => '07:00', 'close' => '18:30'];
        return ['day' => $day, 'label' => ucfirst($day)] + $entry;
    }

    public static function isWithinSystemHours(?Carbon $at = null): bool
    {
        $at = ($at ?: now())->copy()->timezone(config('app.timezone', 'Asia/Manila'));
        $today = static::todaySchedule($at);
        if (!$today['enabled']) {
            return false;
        }

        $current = $at->format('H:i');
        return $current >= $today['open'] && $current <= $today['close'];
    }

    public static function formatTime(string $time): string
    {
        try {
            return Carbon::createFromFormat('H:i', $time)->format('g:i A');
        } catch (\Throwable $e) {
            return $time;
        }
    }

    public static function todayHoursLabel(?Carbon $at = null): string
    {
        $today = static::todaySchedule($at);
        if (!$today['enabled']) {
            return $today['label'] . ': Closed';
        }
        return $today['label'] . ': ' . static::formatTime($today['open']) . ' – ' . static::formatTime($today['close']);
    }

    /** Backward-compatible alias used by existing web and mobile clients. */
    public static function systemHoursLabel(): string
    {
        return static::todayHoursLabel();
    }

    public static function closedMessage(?Carbon $at = null): string
    {
        $today = static::todaySchedule($at);
        if (!$today['enabled']) {
            return "The IT Center is closed today ({$today['label']}). Please check the operating schedule before submitting a request.";
        }

        return 'The IT Center is currently closed. Today\'s operating hours are '
            . static::formatTime($today['open']) . ' to ' . static::formatTime($today['close']) . '.';
    }

    public static function weeklyOperatingSummary(): string
    {
        return collect(static::operatingSchedule())->map(function ($entry, $day) {
            return ucfirst($day) . ': ' . ($entry['enabled']
                ? static::formatTime($entry['open']) . ' – ' . static::formatTime($entry['close'])
                : 'Closed');
        })->join('; ');
    }

    public static function versionHistory(): array
    {
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

    public static function saveVersionLog(string $version, string $notes, ?string $releasedAt = null, ?string $updatedBy = null): void
    {
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
}
