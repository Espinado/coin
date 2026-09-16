<?php

namespace App\Http\Requests\Admin;

use App\Models\Admin;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge([
                'email' => Str::lower(trim($this->string('email')->toString())),
            ]);
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * @throws ValidationException
     */
    public function validateCredentials(): Admin
    {
        $this->ensureIsNotRateLimited();

        $email = $this->string('email')->toString();
        $admin = Admin::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if (! $admin || ! Hash::check($this->string('password')->toString(), (string) $admin->password)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('coin.auth.login_failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        return $admin;
    }

    /**
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $admin = $this->validateCredentials();

        Auth::guard('admin')->login($admin, $this->boolean('remember'));
    }

    /**
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

    public function throttleKey(): string
    {
        return Str::transliterate('admin|'.Str::lower($this->string('email')).'|'.$this->ip());
    }
}
