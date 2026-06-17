<?php

namespace App\Domains\User\Http\Requests;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use App\Domains\User\DTOs\LoginData;
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'max:255',
                'email:rfc,dns'
            ],
            'password' => [
                'required',
                'string',
                Password::min(8)
                    ->max(255)
                    ->mixedCase()
                    ->numbers()
            ],
            'remember' => [
                'nullable',
                'boolean'
            ]
        ];
    }

    /**
     * Return data yang telah divalidasi ke DTO LoginData
     */
    public function toDto(): LoginData
    {
        $validated = $this->validated();

        return new LoginData(
            email: trim($validated['email']),
            password: $validated['password'],
            remember: $validated['remember'] ?? false
        );
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
        $minutes = ceil($seconds / 60);

        // jika diatas 60 detik tampilkan "menit", jika dibawah 60 detik tampilkan "detik"
        $message = $minutes > 1 ? "{$minutes} menit" : "{$seconds} detik";

        throw ValidationException::withMessages([
            'email' => "Terlalu banyak percobaan masuk. Silakan coba lagi dalam {$message}.",
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')) . '|' . $this->ip());
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email harus diisi.',
            'email.string' => 'Email harus berupa string.',
            'email.max' => 'Email tidak boleh lebih dari 255 karakter.',
            'email.email' => 'Format email tidak valid.',
            'email.rfc' => "Format email tidak sesuai standar RFC 5322.",
            'email.dns' => 'Domain email tidak valid.',

            'password.required' => 'Password harus diisi.',
            'password.string' => 'Password harus berupa string.',
            'password.min' => 'Password harus terdiri dari minimal 8 karakter.',
            'password.max' => 'Password tidak boleh lebih dari 255 karakter.',
            'password.mixed' => 'Password harus mengandung huruf besar dan kecil.',
            'password.numbers' => 'Password harus mengandung angka.',
        ];
    }
}
