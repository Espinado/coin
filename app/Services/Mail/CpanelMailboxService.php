<?php

namespace App\Services\Mail;

use App\Models\Mailbox;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

class CpanelMailboxService
{
    /**
     * @return array{
     *     driver: string,
     *     domain: string,
     *     mail_host: string,
     *     imap_port: int,
     *     smtp_port: int,
     *     encryption: string,
     *     webmail_url: string,
     *     from_address: string|null,
     *     from_name: string|null,
     * }
     */
    public function serverSettings(): array
    {
        return [
            'driver' => (string) config('coin.mailboxes.driver', 'null'),
            'domain' => (string) config('coin.mailboxes.domain'),
            'mail_host' => (string) config('coin.mailboxes.mail_host'),
            'imap_port' => (int) config('coin.mailboxes.imap_port'),
            'smtp_port' => (int) config('coin.mailboxes.smtp_port'),
            'encryption' => (string) config('coin.mailboxes.encryption'),
            'webmail_url' => (string) config('coin.mailboxes.webmail_url'),
            'from_address' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
        ];
    }

    public function isEnabled(): bool
    {
        return in_array($this->driver(), ['uapi', 'array'], true);
    }

    /**
     * @return list<array{email: string, local_part: string, has_password: bool, quota_mb: int|null}>
     */
    public function mergedList(): array
    {
        $remote = $this->listRemote();
        $local = Mailbox::query()->get()->keyBy(fn (Mailbox $box) => Str::lower($box->email));

        $items = [];

        foreach ($remote as $email) {
            $email = Str::lower($email);
            $record = $local->get($email);

            $items[] = [
                'email' => $email,
                'local_part' => Str::before($email, '@'),
                'has_password' => $record?->hasStoredPassword() ?? false,
                'quota_mb' => $record?->quota_mb,
            ];
        }

        usort($items, static fn (array $a, array $b): int => strcmp($a['email'], $b['email']));

        return $items;
    }

    /**
     * @return list<string>
     */
    public function listRemote(): array
    {
        return match ($this->driver()) {
            'uapi' => $this->listRemoteViaUapi(),
            'array' => $this->listRemoteViaArray(),
            default => throw new MailboxException(__('coin.admin.mailboxes.disabled')),
        };
    }

    public function find(string $localPart): ?array
    {
        $localPart = $this->normalizeLocalPart($localPart);
        $email = $this->emailFor($localPart);

        foreach ($this->mergedList() as $item) {
            if ($item['local_part'] === $localPart || $item['email'] === $email) {
                $record = Mailbox::query()->where('email', $email)->first();

                return [
                    ...$item,
                    'password' => $record?->password,
                ];
            }
        }

        return null;
    }

    public function create(string $localPart, string $password, ?int $quotaMb = null): Mailbox
    {
        $localPart = $this->normalizeLocalPart($localPart);
        $email = $this->emailFor($localPart);

        if ($this->remoteExists($email)) {
            throw new MailboxException(__('coin.admin.mailboxes.already_exists', ['email' => $email]));
        }

        match ($this->driver()) {
            'uapi' => $this->uapiCreate($localPart, $password, $quotaMb),
            'array' => null,
            default => throw new MailboxException(__('coin.admin.mailboxes.disabled')),
        };

        return Mailbox::query()->updateOrCreate(
            ['email' => $email],
            [
                'local_part' => $localPart,
                'password' => $password,
                'quota_mb' => $quotaMb,
            ],
        );
    }

    public function changePassword(string $localPart, string $password): Mailbox
    {
        $localPart = $this->normalizeLocalPart($localPart);
        $email = $this->emailFor($localPart);

        if (! $this->remoteExists($email)) {
            throw new MailboxException(__('coin.admin.mailboxes.not_found', ['email' => $email]));
        }

        match ($this->driver()) {
            'uapi' => $this->uapiPasswd($email, $password),
            'array' => null,
            default => throw new MailboxException(__('coin.admin.mailboxes.disabled')),
        };

        return Mailbox::query()->updateOrCreate(
            ['email' => $email],
            [
                'local_part' => $localPart,
                'password' => $password,
            ],
        );
    }

    public function normalizeLocalPart(string $localPart): string
    {
        $localPart = Str::lower(trim($localPart));
        $localPart = Str::before($localPart, '@');

        if ($localPart === '' || ! preg_match('/^[a-z0-9](?:[a-z0-9._+-]{0,62}[a-z0-9])?$/', $localPart)) {
            throw new MailboxException(__('coin.admin.mailboxes.invalid_local_part'));
        }

        return $localPart;
    }

    public function emailFor(string $localPart): string
    {
        return Str::lower($localPart).'@'.Str::lower((string) config('coin.mailboxes.domain'));
    }

    private function driver(): string
    {
        return Str::lower((string) config('coin.mailboxes.driver', 'null'));
    }

    private function domain(): string
    {
        return Str::lower((string) config('coin.mailboxes.domain'));
    }

    private function remoteExists(string $email): bool
    {
        $email = Str::lower($email);

        return in_array($email, array_map(Str::lower(...), $this->listRemote()), true);
    }

    /**
     * @return list<string>
     */
    private function listRemoteViaUapi(): array
    {
        $payload = $this->uapi('Email', 'list_pops');
        $rows = $payload['result']['data'] ?? [];

        if (! is_array($rows)) {
            return [];
        }

        $domain = $this->domain();
        $emails = [];

        foreach ($rows as $row) {
            $email = Str::lower((string) ($row['email'] ?? ''));
            if ($email === '' || ! str_contains($email, '@')) {
                continue;
            }
            if (Str::after($email, '@') !== $domain) {
                continue;
            }
            $emails[] = $email;
        }

        return array_values(array_unique($emails));
    }

    /**
     * @return list<string>
     */
    private function listRemoteViaArray(): array
    {
        return Mailbox::query()
            ->orderBy('email')
            ->pluck('email')
            ->map(fn (string $email) => Str::lower($email))
            ->all();
    }

    private function uapiCreate(string $localPart, string $password, ?int $quotaMb): void
    {
        $params = [
            'email' => $localPart,
            'domain' => $this->domain(),
            'password' => $password,
        ];

        if ($quotaMb !== null) {
            $params['quota'] = (string) $quotaMb;
        } else {
            $params['quota'] = '0';
        }

        $payload = $this->uapi('Email', 'add_pop', $params);
        $this->assertUapiOk($payload, 'add_pop');
    }

    private function uapiPasswd(string $email, string $password): void
    {
        $payload = $this->uapi('Email', 'passwd_pop', [
            'email' => $email,
            'password' => $password,
        ]);
        $this->assertUapiOk($payload, 'passwd_pop');
    }

    /**
     * @param  array<string, string>  $params
     * @return array<string, mixed>
     */
    private function uapi(string $module, string $func, array $params = []): array
    {
        $binary = (string) config('coin.mailboxes.uapi_binary', 'uapi');
        $command = [$binary, '--output=json', $module, $func];

        foreach ($params as $key => $value) {
            $command[] = $key.'='.$value;
        }

        $result = Process::timeout(60)->run($command);

        if (! $result->successful()) {
            throw new MailboxException(trim($result->errorOutput() ?: $result->output()) ?: __('coin.admin.mailboxes.uapi_failed'));
        }

        $decoded = json_decode($result->output(), true);
        if (! is_array($decoded)) {
            throw new MailboxException(__('coin.admin.mailboxes.uapi_invalid_response'));
        }

        return $decoded;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function assertUapiOk(array $payload, string $func): void
    {
        $status = (int) data_get($payload, 'result.status', 0);
        if ($status === 1) {
            return;
        }

        $errors = data_get($payload, 'result.errors');
        $message = is_array($errors)
            ? implode(' ', array_filter(array_map('strval', $errors)))
            : (string) ($errors ?: __('coin.admin.mailboxes.uapi_failed'));

        throw new MailboxException(trim($message) !== '' ? $message : __('coin.admin.mailboxes.uapi_failed_func', ['func' => $func]));
    }
}
