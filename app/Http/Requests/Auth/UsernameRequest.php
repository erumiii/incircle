<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UsernameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan username PRD F1: 3-30 karakter, huruf/angka/strip/underscore,
     * unik tanpa membedakan huruf besar/kecil (lowercase sebelum cek unique).
     */
    public function rules(): array
    {
        return [
            'username' => [
                'required', 'string', 'min:3', 'max:30',
                'regex:/^[a-zA-Z0-9_-]+$/',
                Rule::unique('users', 'username'),
            ],
        ];
    }
}
