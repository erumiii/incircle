<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
{
    /**
     * PRD F4: wajib (bukan hanya spasi), maks 2000 karakter.
     */
    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:2000', 'regex:/\S/'],
        ];
    }

    public function messages(): array
    {
        return [
            'message.regex' => 'Pesan tidak boleh berisi spasi saja.',
        ];
    }
}
