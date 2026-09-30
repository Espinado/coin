<?php

namespace App\Services;

use App\Models\SystemLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SystemLogService
{
    private static bool $recording = false;

    private static ?bool $tableReady = null;

    /**
     * @param  array<string, mixed>  $context
     */
    public function record(
        string $level,
        string $source,
        string $message,
        array $context = [],
        ?string $channel = null,
        ?Throwable $exception = null,
    ): void {
        if (self::$recording || ! $this->tableReady()) {
            return;
        }

        self::$recording = true;

        try {
            $payload = [
                'level' => $level,
                'source' => $source,
                'channel' => $channel ? mb_substr($channel, 0, 80) : null,
                'message' => mb_substr($message, 0, 500),
                'exception_class' => null,
                'file' => null,
                'line' => null,
                'context' => $context === [] ? null : $this->sanitizeContext($context),
                'occurred_at' => now(),
            ];

            if ($exception instanceof Throwable) {
                $payload['exception_class'] = mb_substr($exception::class, 0, 255);
                $payload['file'] = mb_substr((string) $exception->getFile(), 0, 500);
                $payload['line'] = (int) $exception->getLine();
                $payload['context'] = array_merge($payload['context'] ?? [], [
                    'exception_message' => mb_substr($exception->getMessage(), 0, 1000),
                ]);
            }

            SystemLog::query()->create($payload);
        } catch (Throwable $e) {
            Log::warning('Failed to write system log entry', [
                'error' => $e->getMessage(),
                'original_message' => $message,
            ]);
        } finally {
            self::$recording = false;
        }
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function error(string $source, string $message, array $context = [], ?string $channel = null, ?Throwable $exception = null): void
    {
        $this->record(SystemLog::LEVEL_ERROR, $source, $message, $context, $channel, $exception);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function warning(string $source, string $message, array $context = [], ?string $channel = null, ?Throwable $exception = null): void
    {
        $this->record(SystemLog::LEVEL_WARNING, $source, $message, $context, $channel, $exception);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function critical(string $source, string $message, array $context = [], ?string $channel = null, ?Throwable $exception = null): void
    {
        $this->record(SystemLog::LEVEL_CRITICAL, $source, $message, $context, $channel, $exception);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function recordException(Throwable $exception, string $source = SystemLog::SOURCE_APP, array $context = [], ?string $channel = null): void
    {
        $level = SystemLog::LEVEL_ERROR;
        $message = $exception->getMessage() !== ''
            ? $exception->getMessage()
            : $exception::class;

        $this->record($level, $source, $message, $context, $channel, $exception);
    }

    private function tableReady(): bool
    {
        if (self::$tableReady !== null) {
            return self::$tableReady;
        }

        try {
            self::$tableReady = Schema::hasTable('system_logs');
        } catch (Throwable) {
            self::$tableReady = false;
        }

        return self::$tableReady;
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function sanitizeContext(array $context): array
    {
        $clean = [];

        foreach ($context as $key => $value) {
            if (is_scalar($value) || $value === null) {
                $clean[(string) $key] = is_string($value) ? mb_substr($value, 0, 2000) : $value;

                continue;
            }

            if (is_array($value)) {
                $clean[(string) $key] = $this->sanitizeContext($value);

                continue;
            }

            $clean[(string) $key] = mb_substr(json_encode($value, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) ?: '', 0, 2000);
        }

        return $clean;
    }
}
