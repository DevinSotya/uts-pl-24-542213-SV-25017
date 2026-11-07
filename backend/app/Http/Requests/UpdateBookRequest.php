<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Concerns\LogsValidationFailures;

class UpdateBookRequest extends FormRequest
{
    use LogsValidationFailures;
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
            'title'  => ['sometimes', 'required', 'string', 'max:255'],
            'author' => ['sometimes', 'required', 'string', 'max:255'],
            'year'   => ['sometimes', 'required', 'integer', 'between:1000,' . ((int) date('Y') + 1)],
            'status' => ['sometimes', 'required', 'in:to_read,reading,finished'],
        ];
    }
}
