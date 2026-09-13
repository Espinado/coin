<?php

namespace App\Console\Commands;

use App\Events\SupportTicketMessageSent;
use App\Models\SupportTicketMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Throwable;

class ReverbDiagnoseCommand extends Command
{
    protected $signature = 'coin:reverb-diagnose {--dispatch : Send a test SupportTicketMessageSent event}';

    protected $description = 'Diagnose Reverb / support chat realtime configuration';

    public function handle(): int
    {
        $this->components->info('Coin Reverb diagnostics');

        $default = config('broadcasting.default');
        $reverb = config('broadcasting.connections.reverb');
        $client = $reverb['client'] ?? [];

        $this->line('Broadcast driver: '.$default);

        if ($default !== 'reverb') {
            $this->components->warn('BROADCAST_CONNECTION is not "reverb" — events will not reach Reverb.');
        }

        $this->line(sprintf(
            'Server publish target: %s:%s (%s)',
            $reverb['options']['host'] ?? '?',
            $reverb['options']['port'] ?? '?',
            $reverb['options']['scheme'] ?? '?',
        ));

        $this->line(sprintf(
            'Browser client target: %s:%s (%s)',
            $client['host'] ?? '?',
            $client['port'] ?? '?',
            $client['scheme'] ?? '?',
        ));

        $this->line('Reverb app key set: '.(filled($reverb['key'] ?? null) ? 'yes' : 'no'));
        $this->line('Reverb debug logging: '.((bool) ($reverb['debug'] ?? false) ? 'enabled' : 'disabled'));

        $host = $reverb['options']['host'] ?? '127.0.0.1';
        $port = (int) ($reverb['options']['port'] ?? 8080);
        $this->line('Local Reverb port reachable: '.($this->canConnect($host, $port) ? 'yes' : 'no'));

        $this->line('Reverb process running: '.($this->reverbProcessRunning() ? 'yes' : 'no'));

        $htaccess = public_path('.htaccess');
        $hasProxy = File::exists($htaccess)
            && str_contains(File::get($htaccess), '127.0.0.1:8080/app');
        $this->line('WebSocket proxy in public/.htaccess: '.($hasProxy ? 'found' : 'missing'));

        if (! $hasProxy && ($client['scheme'] ?? '') === 'https') {
            $this->components->warn('HTTPS client without /app WebSocket proxy — browsers cannot reach Reverb on :443.');
        }

        if (($client['host'] ?? '') === 'localhost' || ($client['host'] ?? '') === '127.0.0.1') {
            $this->components->warn('Browser host points to localhost — remote users cannot connect.');
        }

        if ($this->option('dispatch')) {
            $this->dispatchTestEvent();
        }

        $this->showLogTail(storage_path('logs/reverb.log'), 'Reverb daemon log');
        $this->showLogTail(storage_path('logs/reverb-debug.log'), 'Reverb debug log');
        $this->showBroadcastErrors();

        return self::SUCCESS;
    }

    private function canConnect(string $host, int $port): bool
    {
        $connection = @fsockopen($host, $port, $errno, $errstr, 2);

        if ($connection === false) {
            return false;
        }

        fclose($connection);

        return true;
    }

    private function reverbProcessRunning(): bool
    {
        if (! function_exists('exec')) {
            return false;
        }

        $output = [];
        exec('pgrep -f "artisan reverb:start" 2>/dev/null', $output);

        return $output !== [];
    }

    private function dispatchTestEvent(): void
    {
        $message = SupportTicketMessage::query()->latest('id')->first();

        if (! $message) {
            $this->components->warn('No support messages in DB — skipped test dispatch.');

            return;
        }

        try {
            event(new SupportTicketMessageSent($message));
            $this->components->info('Test SupportTicketMessageSent dispatched for message #'.$message->id);
        } catch (Throwable $exception) {
            $this->components->error('Test dispatch failed: '.$exception->getMessage());
            Log::channel('reverb')->error('coin:reverb-diagnose test dispatch failed', [
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function showLogTail(string $path, string $label): void
    {
        if (! File::exists($path)) {
            $this->line($label.': (file missing)');

            return;
        }

        $lines = collect(explode("\n", trim(File::get($path))))->take(-8)->filter();

        $this->newLine();
        $this->line($label.' (last '.$lines->count().' lines):');

        foreach ($lines as $line) {
            $this->line('  '.$line);
        }
    }

    private function showBroadcastErrors(): void
    {
        $laravelLog = storage_path('logs/laravel.log');

        if (! File::exists($laravelLog)) {
            return;
        }

        $matches = collect(explode("\n", File::get($laravelLog)))
            ->filter(fn (string $line) => str_contains($line, 'Support realtime broadcast')
                || str_contains($line, 'BroadcastException')
                || str_contains($line, 'reverb'))
            ->take(-5);

        if ($matches->isEmpty()) {
            return;
        }

        $this->newLine();
        $this->line('Recent broadcast-related lines in laravel.log:');

        foreach ($matches as $line) {
            $this->line('  '.$line);
        }
    }
}
