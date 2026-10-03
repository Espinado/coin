<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Rules\ContactPhone;
use App\Rules\NotDisposableEmail;
use App\Services\TurnstileVerifier;
use App\Support\PhoneCountries;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merged = [];

        if ($this->has('email')) {
            $merged['email'] = Str::lower(trim($this->string('email')->toString()));
        }

        if ($this->has('website')) {
            $merged['website'] = trim($this->string('website')->toString());
        }

        if ($this->has('phone_country')) {
            $merged['phone_country'] = strtoupper(trim($this->string('phone_country')->toString()));
        }

        $phoneCountry = $merged['phone_country'] ?? $this->input('phone_country');
        $rawNational = $this->has('phone_national')
            ? trim($this->string('phone_national')->toString())
            : null;

        if ($rawNational !== null) {
            $merged['phone_national_includes_dial'] = PhoneCountries::nationalIncludesCountryCode(
                is_string($phoneCountry) ? $phoneCountry : null,
                $rawNational,
            );
            $merged['phone_national'] = preg_replace('/\D+/', '', $rawNational) ?? '';
        }

        if ($this->filled('phone_country') || $this->has('phone_national')) {
            $merged['phone'] = PhoneCountries::compose(
                is_string($phoneCountry) ? $phoneCountry : null,
                $merged['phone_national'] ?? $this->input('phone_national'),
            );
        } elseif ($this->has('phone')) {
            $merged['phone'] = ContactPhone::normalize($this->string('phone')->toString());
        }

        if ($merged !== []) {
            $this->merge($merged);
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                new NotDisposableEmail,
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $email = Str::lower(trim((string) $value));

                    if (User::query()->whereRaw('LOWER(email) = ?', [$email])->exists()) {
                        $fail(__('validation.unique', ['attribute' => $attribute]));
                    }
                },
            ],
            'phone_country' => ['required', 'string', Rule::in(PhoneCountries::isos())],
            'phone_national' => [
                'required',
                'string',
                'min:4',
                'max:15',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($this->boolean('phone_national_includes_dial')) {
                        $fail(__('coin.auth.phone_national_no_country_code'));
                    }
                },
            ],
            'phone' => ['required', 'string', 'max:32', new ContactPhone],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'password_confirmation' => ['required'],
            'accept_terms' => ['required', 'accepted'],
            'accept_privacy' => ['required', 'accepted'],
            'accept_risks' => ['required', 'accepted'],
            // Honeypot: real users never fill this; bots often do.
            'website' => ['nullable', 'string', 'max:0'],
            'cf-turnstile-response' => [
                Rule::requiredIf(fn () => app(TurnstileVerifier::class)->enabled()),
                'nullable',
                'string',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $turnstile = app(TurnstileVerifier::class);

                    if (! $turnstile->enabled()) {
                        return;
                    }

                    if (! $turnstile->verify(is_string($value) ? $value : null, $this->ip())) {
                        $fail(__('coin.auth.captcha_failed'));
                    }
                },
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => __('coin.auth.name_required'),
            'email.required' => __('coin.auth.email_required'),
            'email.email' => __('coin.auth.email_invalid'),
            'phone.required' => __('coin.auth.phone_required'),
            'phone_country.required' => __('coin.auth.phone_country_required'),
            'phone_country.in' => __('coin.auth.phone_country_required'),
            'phone_national.required' => __('coin.auth.phone_national_required'),
            'phone_national.min' => __('coin.auth.phone_national_invalid'),
            'phone_national.max' => __('coin.auth.phone_national_invalid'),
            'password.required' => __('coin.auth.password_required'),
            'password.confirmed' => __('coin.auth.password_confirmed'),
            'password_confirmation.required' => __('coin.auth.password_confirm_required'),
            'accept_terms.required' => __('coin.auth.accept_terms_required'),
            'accept_terms.accepted' => __('coin.auth.accept_terms_required'),
            'accept_privacy.required' => __('coin.auth.accept_privacy_required'),
            'accept_privacy.accepted' => __('coin.auth.accept_privacy_required'),
            'accept_risks.required' => __('coin.auth.accept_risks_required'),
            'accept_risks.accepted' => __('coin.auth.accept_risks_required'),
            'website.max' => __('coin.auth.bot_rejected'),
            'cf-turnstile-response.required' => __('coin.auth.captcha_required'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'phone_country' => __('coin.auth.phone_country'),
            'phone_national' => __('coin.auth.phone_national'),
            'accept_terms' => __('coin.auth.accept_terms_attribute'),
            'accept_privacy' => __('coin.auth.accept_privacy_attribute'),
            'accept_risks' => __('coin.auth.accept_risks_attribute'),
        ];
    }
}
