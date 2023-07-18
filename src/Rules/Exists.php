<?php

namespace ZiffMedia\NovaEloquentImagery\Rules;

use Illuminate\Contracts\Validation\InvokableRule;

class Exists implements InvokableRule
{
    public function __invoke($attribute, $value, $fail)
    {
        $formData = json_decode($value, true);

        if (is_null($formData)) {
            $fail('This image is required.');
        }
    }
}
