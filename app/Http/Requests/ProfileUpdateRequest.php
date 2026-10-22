<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ((!$this->filled('first_name') || !$this->filled('last_name')) && $this->filled('name')) {
            $parts = explode(' ', trim((string) $this->input('name')), 2);
            $this->merge([
                'first_name' => $this->filled('first_name') ? $this->input('first_name') : ($parts[0] ?? ''),
                'last_name' => $this->filled('last_name') ? $this->input('last_name') : ($parts[1] ?? ''),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
        ];
    }
}
