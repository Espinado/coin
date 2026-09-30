<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    public const LEVEL_ERROR = 'error';

    public const LEVEL_WARNING = 'warning';

    public const LEVEL_CRITICAL = 'critical';

    public const LEVEL_INFO = 'info';

    public const SOURCE_APP = 'app';

    public const SOURCE_CRON = 'cron';

    public const SOURCE_SCHEDULE = 'schedule';

    public const SOURCE_MAIL = 'mail';

    public const SOURCE_QUEUE = 'queue';

    public const SOURCE_HTTP = 'http';

    protected $fillable = [
        'level',
        'source',
        'channel',
        'message',
        'exception_class',
        'file',
        'line',
        'context',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'context' => 'array',
            'occurred_at' => 'datetime',
            'line' => 'integer',
        ];
    }

    public function levelLabel(): string
    {
        $key = 'coin.admin.system_log_level.'.$this->level;

        return __($key) !== $key ? __($key) : ucfirst((string) $this->level);
    }

    public function sourceLabel(): string
    {
        $key = 'coin.admin.system_log_source.'.$this->source;

        return __($key) !== $key ? __($key) : ucfirst((string) $this->source);
    }

    public function formattedOccurredAt(): string
    {
        return $this->occurred_at?->timezone(config('app.timezone'))->format('M j, Y H:i:s')
            ?? '—';
    }

    public function shortLocation(): string
    {
        if (! $this->file) {
            return '—';
        }

        $file = str_replace('\\', '/', (string) $this->file);
        $base = base_path();
        $baseNormalized = str_replace('\\', '/', $base);

        if (str_starts_with($file, $baseNormalized)) {
            $file = ltrim(substr($file, strlen($baseNormalized)), '/');
        }

        return $this->line ? "{$file}:{$this->line}" : $file;
    }
}
