<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Captcha implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $expected = session('captcha_code');

        if (empty($expected) || strtoupper((string) $value) !== strtoupper($expected)) {
            $fail('Неверный код с картинки.');
        }
    }
}
