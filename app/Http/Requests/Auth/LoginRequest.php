<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Services\LoginHistoryService;

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
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $user = User::where('email', $this->input('email'))->first();

        // Determine failure reason to show an appropriate error message.
        $isDeliveryPersonnel = $user?->role === 'delivery_personnel';

        $authenticated = ! $isDeliveryPersonnel && Auth::attempt(
            $this->only('email', 'password') + ['is_active' => true],
            $this->boolean('remember')
        );

        if (! $authenticated) {
            RateLimiter::hit($this->throttleKey());

            app(LoginHistoryService::class)->record(
                $user,
                (string) $this->input('email'),
                false,
                $this
            );

            $message = $isDeliveryPersonnel
                ? 'Delivery Personnel accounts cannot access TrackingAid Web. Please use the TrackingAid Mobile App to manage your deliveries.'
                : trans('auth.failed');

            throw ValidationException::withMessages([
                'email' => $message,
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        app(LoginHistoryService::class)->record(
            Auth::user(),
            (string) $this->input('email'),
            true,
            $this
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
        app(LoginHistoryService::class)->record(
            User::where('email', $this->input('email'))->first(),
            (string) $this->input('email'),
            false,
            $this
        );

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
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
