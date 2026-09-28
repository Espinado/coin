<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Rules\ContactPhone;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
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

        if ($this->has('phone')) {
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
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $email = Str::lower(trim((string) $value));

                    if (User::query()->whereRaw('LOWER(email) = ?', [$email])->exists()) {
                        $fail(__('validation.unique', ['attribute' => $attribute]));
                    }
                },
            ],
            'phone' => ['required', 'string', 'max:32', new ContactPhone],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'password_confirmation' => ['required'],
            'accept_terms' => ['required', 'accepted'],
            'accept_privacy' => ['required', 'accepted'],
            'accept_risks' => ['required', 'accepted'],
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
            'password.required' => __('coin.auth.password_required'),
            'password.confirmed' => __('coin.auth.password_confirmed'),
            'password_confirmation.required' => __('coin.auth.password_confirm_required'),
            'accept_terms.required' => __('coin.auth.accept_terms_required'),
            'accept_terms.accepted' => __('coin.auth.accept_terms_required'),
            'accept_privacy.required' => __('coin.auth.accept_privacy_required'),
            'accept_privacy.accepted' => __('coin.auth.accept_privacy_required'),
            'accept_risks.required' => __('coin.auth.accept_risks_required'),
            'accept_risks.accepted' => __('coin.auth.accept_risks_required'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'accept_terms' => __('coin.auth.accept_terms_attribute'),
            'accept_privacy' => __('coin.auth.accept_privacy_attribute'),
            'accept_risks' => __('coin.auth.accept_risks_attribute'),
        ];
    }
}
