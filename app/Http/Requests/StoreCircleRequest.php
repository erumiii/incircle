<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCircleRequest extends FormRequest
{
    /**
     * PRD F2: nama wajib maks 100, deskripsi opsional maks 500.
     */
    public function rules(): array
    {
        return [
            'group_name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }
}
