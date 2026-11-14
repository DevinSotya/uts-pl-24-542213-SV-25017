<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Log;

/**
 * Mencatat validasi gagal sebagai log WARNING terstruktur.
 * Hanya nama field dan nama aturan yang dicatat, tidak pernah nilai input.
 */
trait LogsValidationFailures
{
    protected function failedValidation(Validator $validator)
    {
        $user = $this->user();

        Log::warning('Validation failed', [
            'event' => 'validation.failed',
            'actor' => $user ? 'user:'.$user->id : 'guest',
            'action' => $this->route()?->getName() ?? $this->method().' /'.$this->path(),
            'reason' => 'validation_failed',
            'failed_rules' => array_map('array_keys', $validator->failed()),
        ]);

        parent::failedValidation($validator);
    }
}