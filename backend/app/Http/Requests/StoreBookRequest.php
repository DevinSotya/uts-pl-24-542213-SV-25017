<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
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
            'title'  => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'year'   => ['required', 'integer', 'between:1000,' . ((int) date('Y') + 1)],
            'status' => ['required', 'in:to_read,reading,finished'],
        ];
    }
}
