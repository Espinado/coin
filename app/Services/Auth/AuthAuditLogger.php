<?php

namespace App\Services\Auth;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthAuditLogger
{
    public function logFailure(
        string $guard,
        string $flow,
        string $stage,
        Request $request,
        array $context = [],
    ): void {
        Log::channel('auth_security')->log(
            $this->levelForStage($stage),
            'Auth failure.',
            array_merge([
                'guard' => $guard,
                'flow' => $flow,
                'stage' => $stage,
                'email' => $context['email'] ?? $this->normalizedEmail($request),
                'subject_id' => $context['subject_id'] ?? null,
                'ip' => $request->ip(),
                'host' => $request->getHost(),
                'user_agent' => Str::limit((string) $request->userAgent(), 255, ''),
                'session_id' => Str::limit((string) $request->session()->getId(), 16, ''),
                'route' => $request->route()?->getName(),
            ], $context),
        );
    }

    public function logValidationFailure(string $guard, string $flow, Request $request, Validator $validator): void
    {
        $this->logFailure($guard, $flow, AuthFailureStage::VALIDATION, $request, [
            'validation_errors' => array_keys($validator->errors()->toArray()),
        ]);
    }

    private function levelForStage(string $stage): string
    {
        return match ($stage) {
            AuthFailureStage::ACCOUNT_BLOCKED,
            AuthFailureStage::LOCKOUT,
            AuthFailureStage::TWO_FACTOR_RATE_LIMIT,
            AuthFailureStage::RATE_LIMIT => 'warning',
            default => 'info',
        };
    }

    private function normalizedEmail(Request $request): ?string
    {
        $email = $request->input('email');

        if (! is_string($email) || trim($email) === '') {
            return null;
        }

        return Str::lower(trim($email));
    }
}
