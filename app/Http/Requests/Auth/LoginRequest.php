<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Services\Auth\AuthAuditLogger;
use App\Services\Auth\AuthFailureStage;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge([
                'email' => Str::lower(trim($this->string('email')->toString())),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Validate credentials and return the matching user.
     *
     * @throws ValidationException
     */
    public function validateCredentials(): User
    {
        $this->ensureIsNotRateLimited();

        $email = $this->string('email')->toString();
        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if (! $user) {
            RateLimiter::hit($this->throttleKey());

            $this->authAuditLogger()->logFailure('web', 'credentials', AuthFailureStage::EMAIL_NOT_FOUND, $this);

            throw ValidationException::withMessages([
                'email' => __('coin.auth.login_email_not_found'),
            ]);
        }

        if (! Hash::check($this->string('password')->toString(), (string) $user->password)) {
            RateLimiter::hit($this->throttleKey());

            $this->authAuditLogger()->logFailure('web', 'credentials', AuthFailureStage::PASSWORD_INVALID, $this, [
                'subject_id' => $user->id,
                'email' => $user->email,
            ]);

            throw ValidationException::withMessages([
                'password' => __('coin.auth.login_password_invalid'),
            ]);
        }

        if ($user->is_blocked) {
            RateLimiter::hit($this->throttleKey());

            $this->authAuditLogger()->logFailure('web', 'credentials', AuthFailureStage::ACCOUNT_BLOCKED, $this, [
                'subject_id' => $user->id,
                'email' => $user->email,
            ]);

            throw ValidationException::withMessages([
                'email' => __('coin.auth.blocked'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        return $user;
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $user = $this->validateCredentials();

        if (! Auth::loginUsingId($user->id, $this->boolean('remember'))) {
            $this->authAuditLogger()->logFailure('web', 'credentials', AuthFailureStage::SESSION_LOGIN_FAILED, $this, [
                'subject_id' => $user->id,
                'email' => $user->email,
            ]);

            throw ValidationException::withMessages([
                'email' => __('coin.auth.login_failed'),
            ]);
        }
    }

    protected function failedValidation(Validator $validator): void
    {
        $this->authAuditLogger()->logValidationFailure('web', 'credentials', $this, $validator);

        parent::failedValidation($validator);
    }

    private function authAuditLogger(): AuthAuditLogger
    {
        return app(AuthAuditLogger::class);
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('coin.auth.login_throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
